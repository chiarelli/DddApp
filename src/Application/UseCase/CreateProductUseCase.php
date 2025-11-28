<?php

namespace Chiarelli\DddApp\Application\UseCase;

use Chiarelli\DddApp\Application\DTO\CreateProductRequest;
use Chiarelli\DddApp\Application\DTO\CreateProductResponse;
use Chiarelli\DddApp\Domain\Repository\ProductRepositoryInterface;
use Chiarelli\DddApp\Domain\Repository\ProductTypeRepositoryInterface;
use Chiarelli\DddApp\Domain\Entity\Product;

final class CreateProductUseCase
{
    private ProductRepositoryInterface $productRepository;
    private ProductTypeRepositoryInterface $productTypeRepository;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        ProductTypeRepositoryInterface $productTypeRepository
    ) {
        $this->productRepository = $productRepository;
        $this->productTypeRepository = $productTypeRepository;
    }

    /**
     * Executa o caso de uso de criação de produto.
     *
     * Passos:
     *  - carrega ProductType (lança InvalidArgumentException se não existir)
     *  - obtém last sequence via repositório (null -> 0)
     *  - calcula next sequence e gera code via Domain
     *  - cria entidade Product e persiste via repositório
     *
     * @param CreateProductRequest $request
     * @return CreateProductResponse
     *
     * @throws \InvalidArgumentException quando ProductType não existe ou quando regras do domínio invalidam
     */
    public function execute(CreateProductRequest $request): CreateProductResponse
    {
        $type = $this->productTypeRepository->findById($request->productTypeId);
        if ($type === null) {
            throw new \InvalidArgumentException('ProductType not found: ' . $request->productTypeId);
        }

        $lastSequence = $this->productRepository->getLastSequenceForType($type) ?? 0;
        $nextSequence = $lastSequence + 1;

        // Cria entidade e define code a partir do sequence (a entidade valida limites e prefixo)
        $product = new Product($type, $request->name, $request->price);

        // setCodeFromSequence pode lançar InvalidArgumentException se o sequence for inválido
        $product->setCodeFromSequence($nextSequence);

        $this->productRepository->save($product);

        return new CreateProductResponse($product->getCode(), $product->getName(), $product->getPrice());
    }
}