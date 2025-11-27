<?php

namespace Chiarelli\DddApp\Infrastructure\Repository;

use Chiarelli\DddApp\Domain\Repository\ProductTypeRepositoryInterface;
use Chiarelli\DddApp\Domain\Entity\ProductType as DomainProductType;
use app\models\ProductType as ProductTypeAR;

/**
 * Repositório que mapeia ProductType ActiveRecord <-> Entidade de Domínio.
 *
 * Observação: depende do bootstrap do Yii (autoloader e ActiveRecord).
 */
final class YiiProductTypeRepository implements ProductTypeRepositoryInterface
{
    public function findById(int $id): ?DomainProductType
    {
        $ar = ProductTypeAR::findOne($id);
        if ($ar === null) {
            return null;
        }

        // Converte AR para entidade de domínio
        return new DomainProductType((string)$ar->name, (int)$ar->id);
    }
}