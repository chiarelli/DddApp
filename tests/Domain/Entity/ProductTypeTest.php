<?php

declare(strict_types=1);

namespace Chiarelli\DddApp\Tests\Domain\Entity;

use Chiarelli\DddApp\Domain\Entity\ProductType;
use PHPUnit\Framework\TestCase;

final class ProductTypeTest extends TestCase
{
    public function testNameIsTrimmedAndNormalized(): void
    {
        $pt = new ProductType('  Dress  ');
        $this->assertSame('Dress', $pt->getName());
        $this->assertSame('dress', $pt->getNormalizedName());
    }

    public function testGetCodePrefixWithoutIdThrows(): void
    {
        $this->expectException(\LogicException::class);
        $pt = new ProductType('Dress', null);
        $pt->getCodePrefix();
    }

    public function testGetCodePrefixWithId(): void
    {
        $pt = new ProductType('Skirt', 2);
        $this->assertSame('02', $pt->getCodePrefix());
    }

    public function testInvalidIdWhenProvided(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new ProductType('T-Shirt', 0);
    }

    public function testEmptyNameThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new ProductType('   ');
    }
}