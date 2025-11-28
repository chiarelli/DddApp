<?php

namespace Chiarelli\DddApp\Domain\Repository;

use Chiarelli\DddApp\Domain\Entity\Product;
use Chiarelli\DddApp\Domain\Entity\ProductType;

interface ProductRepositoryInterface
{
    /**
     * Persiste o produto (criar/atualizar).
     *
     * Implementações de infra podem setar o ID ou outras informações técnicas.
     *
     * @param Product $product
     * @return void
     */
    public function save(Product $product): void;

    /**
     * Retorna o último número de sequência utilizado para o tipo informado.
     *
     * - Retorna int (ex.: 5) quando houver registros.
     * - Retorna null quando não houver registros para esse tipo.
     *
     * @param ProductType $type
     * @return int|null
     */
    public function getLastSequenceForType(ProductType $type): ?int;
}