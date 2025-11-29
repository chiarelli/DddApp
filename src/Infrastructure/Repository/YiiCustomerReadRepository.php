<?php

namespace Chiarelli\DddApp\Infrastructure\Repository;

use app\models\Customer as CustomerAR;
use Chiarelli\DddApp\Domain\Repository\CustomerReadRepositoryInterface;
use Chiarelli\DddApp\Infrastructure\Assembler\CustomerAssembler;
use Chiarelli\DddApp\Infrastructure\Assembler\PersonAssembler;

/**
 * YiiCustomerReadRepository
 *
 * Loads customers with their linked people using AR relations and maps them to domain entities.
 *
 * Returns an array of entries:
 * [
 *   [
 *     'customer' => \Chiarelli\DddApp\Domain\Entity\Customer,
 *     'people' => [
 *         ['person' => \Chiarelli\DddApp\Domain\Entity\Person, 'relationship' => string],
 *         ...
 *     ]
 *   ],
 *   ...
 * ]
 */
final class YiiCustomerReadRepository implements CustomerReadRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function findAllWithPeople(): array
    {
        $rows = [];

        // Eager load the pivot relation and the linked person to avoid N+1 queries.
        $customers = CustomerAR::find()
            ->with(['customerRelationshipPeople.person'])
            ->all();

        foreach ($customers as $custAr) {
            // Map AR -> Domain
            $customerDomain = CustomerAssembler::toDomain($custAr);

            $peoplePairs = [];
            foreach ($custAr->customerRelationshipPeople as $crp) {
                $personAr = $crp->person;
                if ($personAr === null) {
                    continue;
                }

                $personDomain = PersonAssembler::toDomain($personAr);
                $peoplePairs[] = [
                    'person' => $personDomain,
                    'relationship' => (string)$crp->relationship,
                ];
            }

            $rows[] = [
                'customer' => $customerDomain,
                'people' => $peoplePairs,
            ];
        }

        return $rows;
    }

    /**
     * {@inheritdoc}
     */
    public function findAllWithPeoplePaginated(array $filters, int $page, int $pageSize): array
    {
        $rows = [];

        $query = CustomerAR::find()->alias('c');

        // Apply fullname filter if present
        if (!empty($filters['q'])) {
            $q = trim((string)$filters['q']);
            $query->andWhere(['like', 'c.fullname', $q]);
        }

        // Eager load pivot -> person to avoid N+1 for the subsequent mapping
        $query->with(['customerRelationshipPeople.person']);
        $query->orderBy(['c.fullname' => SORT_ASC, 'c.id' => SORT_ASC]);

        $offset = max(0, ($page - 1) * $pageSize);
        $query->offset($offset)->limit($pageSize);

        $customers = $query->all();

        foreach ($customers as $custAr) {
            $customerDomain = CustomerAssembler::toDomain($custAr);

            $peoplePairs = [];
            foreach ($custAr->customerRelationshipPeople as $crp) {
                $personAr = $crp->person;
                if ($personAr === null) {
                    continue;
                }
                $personDomain = PersonAssembler::toDomain($personAr);
                $peoplePairs[] = [
                    'person' => $personDomain,
                    'relationship' => (string)$crp->relationship,
                ];
            }

            $rows[] = [
                'customer' => $customerDomain,
                'people' => $peoplePairs,
            ];
        }

        return $rows;
    }

    /**
     * {@inheritdoc}
     */
    public function countAll(array $filters): int
    {
        $query = CustomerAR::find()->alias('c');

        if (!empty($filters['q'])) {
            $q = trim((string)$filters['q']);
            $query->andWhere(['like', 'c.fullname', $q]);
        }

        return (int)$query->count();
    }
}