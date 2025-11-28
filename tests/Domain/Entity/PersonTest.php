<?php

declare(strict_types=1);

namespace Chiarelli\DddApp\Tests\Domain\Entity;

use Chiarelli\DddApp\Domain\Entity\Person;
use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\TestCase;

final class PersonTest extends TestCase
{
    public function testEmptyFirstNameThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Person('   ', '1992-03-01');
    }

    public function testFutureBirthdateThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Person('Bob', '2099-01-01');
    }

    public function testGetAgeWithReference(): void
    {
        $p = new Person('Maria', '2000-02-29', null, null, null);
        $ref = new DateTimeImmutable('2021-02-28');
        $age = $p->getAge($ref);
        $this->assertSame(20, $age->value());
    }

    public function testLeapDayBirthdayBeforeAndAfter(): void
    {
        $p = new Person('Leap Person', '2000-02-29');
        $refBefore = new DateTimeImmutable('2021-02-28');
        $refAfter = new DateTimeImmutable('2021-03-01');

        $ageBefore = $p->getAge($refBefore);
        $ageAfter = $p->getAge($refAfter);

        $this->assertSame(20, $ageBefore->value());
        $this->assertSame(21, $ageAfter->value());
    }

    public function testBornTodayReturnsZero(): void
    {
        $today = new DateTimeImmutable('2025-11-28');
        $p = new Person('Baby Today', '2025-11-28');
        $age = $p->getAge($today);
        $this->assertSame(0, $age->value());
    }

    public function testBirthdateExactlyOneYearAgo(): void
    {
        $reference = new DateTimeImmutable('2025-11-28');
        $oneYearAgo = new DateTimeImmutable('2024-11-28');

        $p = new Person('One Year', $oneYearAgo->format('Y-m-d'));
        $age = $p->getAge($reference);

        $this->assertSame(1, $age->value());
    }

    public function testAcceptsDateTimeInterfaceWithTimezone(): void
    {
        $birthWithTime = new DateTimeImmutable('1995-07-10 23:30:00', new DateTimeZone('America/Sao_Paulo'));
        $referenceUtc = new DateTimeImmutable('2025-07-10 00:00:00', new DateTimeZone('UTC'));

        $p = new Person('Timezone Person', $birthWithTime);
        $age = $p->getAge($referenceUtc);

        $this->assertSame(29, $age->value());
    }

    public function testIdWhenProvided(): void
    {
        $p = new Person('With ID', '1990-01-01', null, null, null, 99);
        $this->assertSame(99, $p->getId());
    }

    public function testInvalidIdThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Person('Bad ID', '1990-01-01', null, null, null, 0);
    }
}