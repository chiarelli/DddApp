<?php

declare(strict_types=1);

namespace Chiarelli\DddApp\Tests\Application;

use Chiarelli\DddApp\Application\UseCase\ListCustomersWithPeopleQuery;
use Chiarelli\DddApp\Domain\Entity\Customer;
use Chiarelli\DddApp\Domain\Entity\Person;
use Chiarelli\DddApp\Domain\Repository\CustomerReadRepositoryInterface;
use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\TestCase;

final class ListCustomersWithPeopleQueryTest extends TestCase
{
    public function testExecuteMapsCustomersAndPeopleToDtosAndComputesAges(): void
    {
        // Reference date to make ages deterministic
        $reference = new DateTimeImmutable('2025-11-28', new DateTimeZone('UTC'));

        // Arrange: create domain entities
        $customer1 = new Customer('Alice Example', '2000-11-28', 1); // age 25 on reference
        $person1 = new Person('Bob', '1995-07-10', null, null, 'M', 11); // age 30 on reference

        // person with time + timezone to ensure normalization works
        $person2Birth = new DateTimeImmutable('1990-12-01 23:30:00', new DateTimeZone('America/Sao_Paulo'));
        $person2 = new Person('Carol', $person2Birth, null, null, 'F', 12); // age depends on reference, here should be 34 on 2025-11-28

        $rows = [
            [
                'customer' => $customer1,
                'people' => [
                    ['person' => $person1, 'relationship' => 'son'],
                    ['person' => $person2, 'relationship' => 'friend'],
                ],
            ],
        ];

        // Fake repository
        $repo = new class($rows) implements CustomerReadRepositoryInterface {
            private array $rows;
            public function __construct(array $rows) { $this->rows = $rows; }
            public function findAllWithPeople(): array { return $this->rows; }
        };

        $useCase = new ListCustomersWithPeopleQuery($repo);

        // Act
        $result = $useCase->execute($reference);

        // Assert
        $this->assertCount(1, $result);
        $entry = $result[0];

        // Customer assertions
        $this->assertSame(1, $entry->customer->id);
        $this->assertSame('Alice Example', $entry->customer->fullName);
        $this->assertSame('2000-11-28', $entry->customer->birthdate);
        $this->assertSame(25, $entry->customer->age);

        // People assertions: order preserved
        $this->assertCount(2, $entry->people);

        $p1 = $entry->people[0];
        $this->assertSame(11, $p1->id);
        $this->assertSame('Bob', $p1->firstName);
        $this->assertSame('1995-07-10', $p1->birthdate);
        $this->assertSame(30, $p1->age);
        $this->assertSame('son', $p1->relationship);

        $p2 = $entry->people[1];
        $this->assertSame(12, $p2->id);
        $this->assertSame('Carol', $p2->firstName);
        $this->assertSame('1990-12-01', $p2->birthdate);
        // born 1990-12-01 -> on 2025-11-28 hasn't had 35th birthday, so age 34
        $this->assertSame(34, $p2->age);
        $this->assertSame('friend', $p2->relationship);
    }
}