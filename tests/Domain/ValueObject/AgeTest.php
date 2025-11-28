<?php

declare(strict_types=1);

namespace Chiarelli\DddApp\Tests\Domain\ValueObject;

use Chiarelli\DddApp\Domain\ValueObject\Age;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class AgeTest extends TestCase
{
    public function testAgeIsZeroWhenBornToday(): void
    {
        $today = new DateTimeImmutable('2025-11-28'); // deterministic reference
        $age = Age::fromBirthdate('2025-11-28', $today);
        $this->assertSame(0, $age->value());
    }

    public function testAgeBeforeAndAfterBirthday(): void
    {
        $birth = '1990-05-20';
        $referenceBefore = new DateTimeImmutable('2020-05-19');
        $referenceOn = new DateTimeImmutable('2020-05-20');
        $aBefore = Age::fromBirthdate($birth, $referenceBefore);
        $aOn = Age::fromBirthdate($birth, $referenceOn);
        $this->assertSame(29, $aBefore->value());
        $this->assertSame(30, $aOn->value());
    }

    public function testLeapYearBirthday(): void
    {
        // Born on Feb 29, 2000. On 2021-02-28 should be 20, on 2021-03-01 should be 21.
        $birth = '2000-02-29';
        $ref1 = new DateTimeImmutable('2021-02-28');
        $ref2 = new DateTimeImmutable('2021-03-01');
        $a1 = Age::fromBirthdate($birth, $ref1);
        $a2 = Age::fromBirthdate($birth, $ref2);
        $this->assertSame(20, $a1->value());
        $this->assertSame(21, $a2->value());
    }

    public function testFutureBirthdateThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Age::fromBirthdate('2099-01-01', new DateTimeImmutable('2025-11-28'));
    }

    public function testInvalidFormatThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Age::fromBirthdate('01-01-2000');
    }
}