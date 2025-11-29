<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Chiarelli\DddApp\Infrastructure\ReadModel\ArrayToDtoMapper;
use Chiarelli\DddApp\Application\DTO\CustomerWithPeopleDto;
use Chiarelli\DddApp\Application\DTO\CustomerDto;
use Chiarelli\DddApp\Application\DTO\LinkedPersonDto;
use Chiarelli\DddApp\Domain\ValueObject\Age;

final class ArrayToDtoMapperTest extends TestCase
{
    public function testMapRowsToDtosProducesExpectedDtos(): void
    {
        $rows = [
            [
                'c_id' => 1001,
                'c_fullname' => 'SeedBulkCustomer 1001',
                'c_birthdate' => '1980-01-01',
                'p_id' => 2001,
                'p_first_name' => 'SeedPerson2001',
                'p_middle_name' => null,
                'p_last_name' => 'Bulk',
                'p_birthdate' => '2000-06-15',
                'p_gender' => 'F',
                'relationship' => 'child',
            ],
            [
                'c_id' => 1001,
                'c_fullname' => 'SeedBulkCustomer 1001',
                'c_birthdate' => '1980-01-01',
                'p_id' => 2002,
                'p_first_name' => 'SeedPerson2002',
                'p_middle_name' => null,
                'p_last_name' => 'Bulk',
                'p_birthdate' => '1998-03-10',
                'p_gender' => 'M',
                'relationship' => 'spouse',
            ],
            [
                'c_id' => 1002,
                'c_fullname' => 'SeedBulkCustomer 1002',
                'c_birthdate' => '1975-05-05',
                'p_id' => null,
                'p_first_name' => null,
                'p_birthdate' => null,
                'relationship' => null,
            ],
        ];

        $reference = new \DateTimeImmutable('2025-11-28');

        $dtos = ArrayToDtoMapper::mapRowsToDtos($rows, $reference);

        $this->assertIsArray($dtos);
        $this->assertCount(2, $dtos);

        /** @var CustomerWithPeopleDto $first */
        $first = $dtos[0];
        $this->assertInstanceOf(CustomerWithPeopleDto::class, $first);
        $this->assertInstanceOf(CustomerDto::class, $first->customer);
        $this->assertSame(1001, $first->customer->id);
        $this->assertSame('SeedBulkCustomer 1001', $first->customer->fullName);
        $this->assertSame('1980-01-01', $first->customer->birthdate);
        // Age from 1980-01-01 to 2025-11-28 is 45
        $this->assertSame(45, $first->customer->age);

        $this->assertCount(2, $first->people);
        $p1 = $first->people[0]['person'];
        $this->assertInstanceOf(LinkedPersonDto::class, $p1);
        $this->assertSame(2001, $p1->id);
        $this->assertSame('SeedPerson2001', $p1->firstName);
        $this->assertSame('2000-06-15', $p1->birthdate);
        $this->assertSame(Age::fromBirthdate('2000-06-15', $reference)->value(), $p1->age);

        /** @var CustomerWithPeopleDto $second */
        $second = $dtos[1];
        $this->assertInstanceOf(CustomerWithPeopleDto::class, $second);
        $this->assertInstanceOf(CustomerDto::class, $second->customer);
        $this->assertSame(1002, $second->customer->id);
        $this->assertSame('SeedBulkCustomer 1002', $second->customer->fullName);
        // No people linked
        $this->assertCount(0, $second->people);
    }
}