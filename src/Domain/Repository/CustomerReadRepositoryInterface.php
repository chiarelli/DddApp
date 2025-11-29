<?php

namespace Chiarelli\DddApp\Domain\Repository;

interface CustomerReadRepositoryInterface
{
    /**
     * Returns an array of records each containing:
     * [
     *   'customer' => \Chiarelli\DddApp\Domain\Entity\Customer,
     *   'people' => [
     *       ['person' => \Chiarelli\DddApp\Domain\Entity\Person, 'relationship' => string],
     *       ...
     *   ]
     * ]
     *
     * @return array
     */
    public function findAllWithPeople(): array;

    /**
     * Paginado: retorna linhas formatadas como em findAllWithPeople, mas aplicando filtros e paginação.
     *
     * @param array $filters Associative array of filters. Supported: ['q' => string] (fullname LIKE)
     * @param int $page 1-based page number
     * @param int $pageSize items per page
     * @return array
     */
    public function findAllWithPeoplePaginated(array $filters, int $page, int $pageSize): array;

    /**
     * Conta o total de customers que casam com os filtros fornecidos (usado para paginação).
     *
     * @param array $filters Associative array of filters. Supported: ['q' => string] (fullname LIKE)
     * @return int
     */
    public function countAll(array $filters): int;
}