<?php

declare(strict_types=1);

namespace Chiarelli\DddApp\Tests\Domain\Entity;

use Chiarelli\DddApp\Domain\Entity\Product;
use Chiarelli\DddApp\Domain\Entity\ProductType;
use PHPUnit\Framework\TestCase;

final class ProductTest extends TestCase
{
    public function testEmptyNameThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Product(new ProductType('Dress', 1), '', 10.0);
    }

    public function testNonPositivePriceThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Product(new ProductType('Dress', 1), 'Item', 0.0);
    }

    public function testSetCodeFromSequenceGeneratesExpectedFormat(): void
    {
        $type = new ProductType('Dress', 1);
        $product = new Product($type, 'Item', 99.9);
        $product->setCodeFromSequence(5);

        $this->assertSame('010005', $product->getCode());
    }

    public function testGenerateCodeHelper(): void
    {
        $type = new ProductType('Skirt', 2);
        $code = Product::generateCode($type, 1);
        $this->assertSame('020001', $code);
    }

    public function testSetCodeWithWrongPrefixThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $type = new ProductType('Skirt', 2);
        $product = new Product($type, 'Saia', 50.0);
        // prefix 99 não bate com type id 2
        $product->setCode('990001');
    }

    public function testSetCodeWithInvalidFormatThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $type = new ProductType('Skirt', 2);
        $product = new Product($type, 'Saia', 50.0);
        // 5 dígitos apenas
        $product->setCode('02001');
    }

    public function testChangeStatusRejectsInvalid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $type = new ProductType('Dress', 1);
        $product = new Product($type, 'Vestido', 100.0);
        $product->changeStatus('Archived'); // não permitido
    }

    public function testSetValidCodeDirectly(): void
    {
        $type = new ProductType('Skirt', 2);
        $product = new Product($type, 'Saia', 50.0);
        $product->setCode('020123');
        $this->assertSame('020123', $product->getCode());
    }
}