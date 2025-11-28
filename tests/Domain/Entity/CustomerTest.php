<?php

declare(strict_types=1);

namespace Chiarelli\DddApp\Tests\Domain\Entity;

use Chiarelli\DddApp\Domain\Entity\Customer;
use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\TestCase;

final class CustomerTest extends TestCase
{
    public function testEmptyFullNameThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Customer('   ', '1990-01-01');
    }

    public function testFutureBirthdateThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Customer('John Doe', '2099-01-01');
    }

    public function testGetAgeWithReference(): void
    {
        $c = new Customer('Alice Example', '1985-06-15');
        $ref = new DateTimeImmutable('2020-06-16');
        $age = $c->getAge($ref);
        $this->assertSame(35, $age->value());
    }

    public function testLeapDayBirthdayBeforeAndAfter(): void
    {
        $c = new Customer('Leap Customer', '2000-02-29');
        $refBefore = new DateTimeImmutable('2021-02-28');
        $refAfter = new DateTimeImmutable('2021-03-01');

        $ageBefore = $c->getAge($refBefore);
        $ageAfter = $c->getAge($refAfter);

        $this->assertSame(20, $ageBefore->value());
        $this->assertSame(21, $ageAfter->value());
    }

    public function testBornTodayReturnsZero(): void
    {
        $today = new DateTimeImmutable('2025-11-28');
        $c = new Customer('Baby Today', '2025-11-28');
        $age = $c->getAge($today);
        $this->assertSame(0, $age->value());
    }

    public function testBirthdateExactlyOneYearAgo(): void
    {
        $reference = new DateTimeImmutable('2025-11-28');
        $oneYearAgo = new DateTimeImmutable('2024-11-28');

        $c = new Customer('One Year', $oneYearAgo->format('Y-m-d'));
        $age = $c->getAge($reference);

        $this->assertSame(1, $age->value());
    }

    public function testAcceptsDateTimeInterfaceWithTimezone(): void
    {
        // Birthdate with time and timezone; entity should normalize to date-only.
        $birthWithTime = new DateTimeImmutable('2000-01-15 13:45:00', new DateTimeZone('America/Sao_Paulo'));
        $referenceUtc = new DateTimeImmutable('2025-01-15 00:00:00', new DateTimeZone('UTC'));

        $c = new Customer('Timezone Person', $birthWithTime);
        $age = $c->getAge($referenceUtc);

        $this->assertSame(25, $age->value());
    }

    public function testIdWhenProvided(): void
    {
        $c = new Customer('With ID', '1990-01-01', 42);
        $this->assertSame(42, $c->getId());
    }

    public function testInvalidIdThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Customer('Bad ID', '1990-01-01', 0);
    }
}