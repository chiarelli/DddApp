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
}