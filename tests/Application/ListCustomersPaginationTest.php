<?php

declare(strict_types=1);

namespace Chiarelli\DddApp\Tests\Application;

use Chiarelli\DddApp\Application\UseCase\ListCustomersWithPeopleQuery;
use Chiarelli\DddApp\Domain\Repository\CustomerReadRepositoryInterface;
use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\TestCase;

final class ListCustomersPaginationTest extends TestCase
{
    public function testExecuteForwardsFiltersAndPaginationToRepository(): void
    {
        $captor = new class implements CustomerReadRepositoryInterface {
            public array $lastFilters = [];
            public int $lastPage = 0;
            public int $lastPageSize = 0;

            public function findAllWithPeople(): array { return []; }
            public function findAllWithPeoplePaginated(array $filters, int $page, int $pageSize): array {
                $this->lastFilters = $filters;
                $this->lastPage = $page;
                $this->lastPageSize = $pageSize;
                return [];
            }
            public function countAll(array $filters): int {
                $this->lastFilters = $filters;
                return 0;
            }
        };

        $useCase = new ListCustomersWithPeopleQuery($captor);

        $reference = new DateTimeImmutable('2025-11-28', new DateTimeZone('UTC'));
        $result = $useCase->execute(3, 15, '  Alice  ', $reference);

        // ensure repository received normalized filter
        $this->assertArrayHasKey('q', $captor->lastFilters);
        $this->assertSame('Alice', $captor->lastFilters['q']);

        // ensure pagination forwarded correctly
        $this->assertSame(3, $captor->lastPage);
        $this->assertSame(15, $captor->lastPageSize);

        // ensure result structure includes keys
        $this->assertArrayHasKey('entries', $result);
        $this->assertArrayHasKey('totalCount', $result);
        $this->assertSame(3, $result['page']);
        $this->assertSame(15, $result['pageSize']);
    }
}