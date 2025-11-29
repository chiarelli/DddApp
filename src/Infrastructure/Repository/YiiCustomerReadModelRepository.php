<?php

namespace Chiarelli\DddApp\Infrastructure\Repository;

use Chiarelli\DddApp\Domain\Repository\CustomerReadRepositoryInterface;
use Chiarelli\DddApp\Infrastructure\ReadModel\ArrayToDtoMapper;
use yii\db\Query;

/**
 * Read-model repository that selects minimal columns with JOINs and returns DTOs directly.
 *
 * Note: returns entries shaped as:
 * [
 *   ['customer' => \Chiarelli\DddApp\Application\DTO\CustomerDto, 'people' => [['person' => LinkedPersonDto, 'relationship' => string], ...]],
 *   ...
 * ]
 */
final class YiiCustomerReadModelRepository implements CustomerReadRepositoryInterface
{
    public function findAllWithPeople(): array
    {
        // default: return first page with generous page size
        return $this->findAllWithPeoplePaginated([], 1, 1000);
    }

    public function findAllWithPeoplePaginated(array $filters, int $page, int $pageSize): array
    {
        $db = \Yii::$app->db;

        $query = (new Query())->from(['c' => $db->quoteTableName($db->tablePrefix . 'customer')]);

        if (!empty($filters['q'])) {
            $q = trim((string)$filters['q']);
            $query->andWhere(['like', 'c.fullname', $q]);
        }

        $query->orderBy(['c.fullname' => SORT_ASC, 'c.id' => SORT_ASC]);

        // 1) Get paginated customer IDs to avoid incorrect pagination due to JOIN duplication.
        $offset = max(0, ($page - 1) * $pageSize);
        $customerIds = (clone $query)
            ->select('c.id')
            ->offset($offset)
            ->limit($pageSize)
            ->column();

        if (empty($customerIds)) {
            return [];
        }

        // 2) Fetch minimal joined rows for the customerIds
        $rowsQuery = (new Query())
            ->select([
                'c_id' => 'c.id',
                'c_fullname' => 'c.fullname',
                'c_birthdate' => 'c.birthdate',
                'p_id' => 'p.id',
                'p_first_name' => 'p.first_name',
                'p_middle_name' => 'p.middle_name',
                'p_last_name' => 'p.last_name',
                'p_birthdate' => 'p.birthdate',
                'p_gender' => 'p.gender',
                'relationship' => 'cr.relationship',
            ])
            ->from(['c' => $db->quoteTableName($db->tablePrefix . 'customer')])
            ->leftJoin(['cr' => $db->quoteTableName($db->tablePrefix . 'customer_relationship_person')], 'cr.customer_id = c.id')
            ->leftJoin(['p' => $db->quoteTableName($db->tablePrefix . 'person')], 'p.id = cr.person_id')
            ->where(['in', 'c.id', $customerIds])
            ->orderBy(['c.fullname' => SORT_ASC, 'c.id' => SORT_ASC, 'p.id' => SORT_ASC]);

        $rows = $rowsQuery->all();

        // 3) Map rows to DTOs
        return ArrayToDtoMapper::mapRowsToDtos($rows);
    }

    public function countAll(array $filters): int
    {
        $db = \Yii::$app->db;
        $query = (new Query())->from(['c' => $db->quoteTableName($db->tablePrefix . 'customer')]);
        if (!empty($filters['q'])) {
            $q = trim((string)$filters['q']);
            $query->andWhere(['like', 'c.fullname', $q]);
        }
        return (int)$query->count();
    }
}
