<?php

declare(strict_types=1);

namespace Chiarelli\DddApp\Tests\Application;

use Chiarelli\DddApp\Application\UseCase\ListCustomersWithPeopleQuery;
use Chiarelli\DddApp\Domain\Repository\CustomerReadRepositoryInterface;
use Chiarelli\DddApp\Application\Port\CacheProviderInterface;
use Chiarelli\DddApp\Domain\Entity\Customer;
use Chiarelli\DddApp\Domain\Entity\Person;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class ListCustomersCacheTest extends TestCase
{
  public function testCacheKeyTtlAndTagsAreGeneratedCorrectly(): void
  {
    // Enable cache via env
    putenv('CUSTOMERS_LIST_CACHE_ENABLED=true');
    putenv('CUSTOMERS_LIST_CACHE_TTL=123');
    putenv('CUSTOMERS_LIST_PAGE_SIZE_DEFAULT=20');

    // Prepare domain objects
    $customer = new Customer('Alice Example', '1980-01-01', 1);
    $person1 = new Person('Bob', '2000-06-15', null, null, 'M', 11);
    $person2 = new Person('Carol', '1995-12-01', null, null, 'F', 12);

    // Fake repository that returns one row
    $repo = new class($customer, $person1, $person2) implements CustomerReadRepositoryInterface {
      private $customer;
      private $p1;
      private $p2;
      public function __construct($customer, $p1, $p2)
      {
        $this->customer = $customer;
        $this->p1 = $p1;
        $this->p2 = $p2;
      }
      public function findAllWithPeople(): array
      {
        return $this->findAllWithPeoplePaginated([], 1, 20);
      }
      public function findAllWithPeoplePaginated(array $filters, int $page, int $pageSize): array
      {
        return [
          [
            'customer' => $this->customer,
            'people' => [
              ['person' => $this->p1, 'relationship' => 'son'],
              ['person' => $this->p2, 'relationship' => 'friend'],
            ],
          ],
        ];
      }
      public function countAll(array $filters): int
      {
        return 1;
      }
    };

    // Fake cache provider that captures key, ttl and captured tags from producer
    $fakeCache = new class implements CacheProviderInterface {
      public $lastKey = null;
      public $lastTtl = null;
      public $capturedTags = null;

      public function remember(string $key, int $ttl, array $tags, callable $producer)
      {
        // not used in this test
        $this->lastKey = $key;
        $this->lastTtl = $ttl;
        $this->capturedTags = $tags;
        return $producer();
      }

      public function rememberWithComputedTags(string $key, int $ttl, callable $producer)
      {
        $this->lastKey = $key;
        $this->lastTtl = $ttl;
        $result = $producer();
        if (is_array($result) && count($result) === 2) {
          [$value, $tags] = $result;
          $this->capturedTags = $tags;
          return $value;
        }
        // fallback
        $this->capturedTags = [];
        return $result;
      }
    };

    $useCase = new ListCustomersWithPeopleQuery($repo, $fakeCache);

    $reference = new DateTimeImmutable('2025-11-28');
    $result = $useCase->execute(1, 20, null, $reference);

    // Assertions on structure
    $this->assertArrayHasKey('entries', $result);
    $this->assertArrayHasKey('totalCount', $result);

    // Key and TTL captured
    $this->assertNotNull($fakeCache->lastKey, 'Cache key should be captured');
    $this->assertStringStartsWith('customers:list:', $fakeCache->lastKey);
    $this->assertSame(123, $fakeCache->lastTtl);

    // Tags should include customer_1, link_customer_1, person_11 and person_12
    $expected = ['customer_1', 'link_customer_1', 'person_11', 'person_12'];
    foreach ($expected as $tag) {
      $this->assertContains($tag, $fakeCache->capturedTags, "Expected tag {$tag} in captured tags");
    }

    // Basic content assertions
    $entries = $result['entries'];
    $this->assertCount(1, $entries);
    $entry = $entries[0];
    $this->assertSame('Alice Example', $entry->customer->fullName);
    $this->assertCount(2, $entry->people);
  }
}
