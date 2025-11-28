<?php

declare(strict_types=1);

namespace Chiarelli\DddApp\Tests\Application;

use Chiarelli\DddApp\Application\DTO\CreateProductRequest;
use Chiarelli\DddApp\Application\UseCase\CreateProductUseCase;
use Chiarelli\DddApp\Domain\Repository\ProductRepositoryInterface;
use Chiarelli\DddApp\Domain\Repository\ProductTypeRepositoryInterface;
use Chiarelli\DddApp\Domain\Entity\ProductType;
use Chiarelli\DddApp\Domain\Entity\Product;
use PHPUnit\Framework\TestCase;

final class CreateProductUseCaseTest extends TestCase
{
    public function testCreatesProductSuccessfullyWhenNoPreviousSequence(): void
    {
        $productType = new ProductType('Dress', 1);

        $productTypeRepo = new class($productType) implements ProductTypeRepositoryInterface {
            private ?ProductType $type;
            public function __construct(?ProductType $type) { $this->type = $type; }
            public function findById(int $id): ?ProductType { return $this->type; }
        };

        $productRepo = new class implements ProductRepositoryInterface {
            public array $saved = [];
            public ?int $lastSequence = null;
            public function save(Product $product): void { $this->saved[] = $product; }
            public function getLastSequenceForType(ProductType $type): ?int { return $this->lastSequence; }
        };

        $useCase = new CreateProductUseCase($productRepo, $productTypeRepo);

        $request = new CreateProductRequest('My Dress', 12.5, 1);
        $response = $useCase->execute($request);

        $this->assertSame('010001', $response->code);
        $this->assertSame('My Dress', $response->name);
        $this->assertSame(12.5, $response->price);

        $this->assertCount(1, $productRepo->saved);
        $saved = $productRepo->saved[0];
        $this->assertInstanceOf(Product::class, $saved);
        $this->assertSame('010001', $saved->getCode());
    }

    public function testThrowsWhenProductTypeNotFound(): void
    {
        $productTypeRepo = new class implements ProductTypeRepositoryInterface {
            public function findById(int $id): ?ProductType { return null; }
        };

        $productRepo = new class implements ProductRepositoryInterface {
            public function save(Product $product): void {}
            public function getLastSequenceForType(ProductType $type): ?int { return null; }
        };

        $useCase = new CreateProductUseCase($productRepo, $productTypeRepo);

        $this->expectException(\InvalidArgumentException::class);
        $useCase->execute(new CreateProductRequest('X', 1.0, 999));
    }

    public function testThrowsWhenNextSequenceTooLarge(): void
    {
        $productType = new ProductType('Shoes', 3);

        $productTypeRepo = new class($productType) implements ProductTypeRepositoryInterface {
            private ?ProductType $type;
            public function __construct(?ProductType $type) { $this->type = $type; }
            public function findById(int $id): ?ProductType { return $this->type; }
        };

        $productRepo = new class implements ProductRepositoryInterface {
            public ?int $lastSequence = 9999;
            public function save(Product $product): void {}
            public function getLastSequenceForType(ProductType $type): ?int { return $this->lastSequence; }
        };

        $useCase = new CreateProductUseCase($productRepo, $productTypeRepo);

        $this->expectException(\InvalidArgumentException::class);
        $useCase->execute(new CreateProductRequest('Huge', 100.0, 3));
    }
}