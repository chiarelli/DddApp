<?php

namespace Chiarelli\DddApp\Domain\Repository;

use Chiarelli\DddApp\Domain\Entity\ProductType;

interface ProductTypeRepositoryInterface
{
    /**
     * Busca um ProductType pelo seu identificador.
     *
     * Retorna null quando não encontrado.
     *
     * @param int $id
     * @return ProductType|null
     */
    public function findById(int $id): ?ProductType;
}