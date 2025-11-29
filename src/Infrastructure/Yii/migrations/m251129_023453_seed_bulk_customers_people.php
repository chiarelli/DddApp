<?php

use yii\db\Migration;

/**
 * class m251129_023453_seed_bulk_customers_people
 * Seeds a set of customers and people and links them via customer_relationship_person pivot.
 *
 * - Customers: IDs 1001..1060, fullname prefixed with 'SeedBulkCustomer '
 * - People: IDs 2001..2120, first_name prefixed with 'SeedPerson'
 * - Pivot: each customer links to 1..3 people, no duplicate (customer_id, person_id)
 *
 * The migration is idempotent: if customers with prefix exist it will skip insertion. 
 */
class m251129_023453_seed_bulk_customers_people extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $db = Yii::$app->db;
        // Guard clause: se já tivermos SeedBulkCustomer, não inserimos novamente
        $exists = (new \yii\db\Query())
            ->from('{{%customer}}')
            ->where(['like', 'fullname', 'SeedBulkCustomer %', false])
            ->exists($db);

        if ($exists) {
            // já existe seed similar, nada a fazer
            return;
        }

        $time = time();

        // --- Customers: 1001..1060 (60 customers) ---
        $customers = [];
        $customerIds = [];
        for ($i = 1001; $i <= 1060; $i++) {
            $gender = ($i % 2 === 0) ? 'F' : 'M';
            $fullname = "SeedBulkCustomer {$i}";
            $nicename = "SeedBulkCustomer {$i}";
            $birthdate = sprintf('%04d-%02d-%02d', 1970 + ($i % 30), 1 + ($i % 12), 1 + ($i % 28));
            $customers[] = [
                $i,          // id
                $gender,     // gender
                $fullname,   // fullname
                $nicename,   // nicename
                $birthdate,  // birthdate
                'Active',    // status
                $time,       // created_at
                $time,       // updated_at
            ];
            $customerIds[] = $i;
        }

        $this->batchInsert('{{%customer}}', [
            'id',
            'gender',
            'fullname',
            'nicename',
            'birthdate',
            'status',
            'created_at',
            'updated_at'
        ], $customers);

        // --- People: 2001..2120 (120 people) ---
        $people = [];
        $personIds = [];
        for ($j = 2001; $j <= 2120; $j++) {
            $first = "SeedPerson{$j}";
            $middle = null;
            $last = "Bulk";
            $birth = sprintf('%04d-%02d-%02d', 1980 + ($j % 30), 1 + ($j % 12), 1 + ($j % 28));
            $gender = ($j % 2 === 0) ? 'F' : 'M';
            $people[] = [
                $j,
                $first,
                $middle,
                $last,
                $birth,
                $gender,
                $time,
                $time,
            ];
            $personIds[] = $j;
        }

        $this->batchInsert('{{%person}}', [
            'id',
            'first_name',
            'middle_name',
            'last_name',
            'birthdate',
            'gender',
            'created_at',
            'updated_at'
        ], $people);

        // --- Pivot: customer_relationship_person ---
        // Vamos distribuir pessoas sequencialmente para clientes: cada customer recebe 1..3 pessoas.
        $pivot = [];
        $pidIndex = 0;
        $personCount = count($personIds);

        foreach ($customerIds as $custId) {
            // decide quantas pessoas vincular (1..3) de maneira determinística
            $links = 1 + ($custId % 3); // 1,2 ou 3
            for ($k = 0; $k < $links; $k++) {
                // escolhe next person id (circular)
                $personId = $personIds[$pidIndex % $personCount];
                $relationship = ['child', 'spouse', 'sibling', 'parent', 'relative'][(int)($custId + $k) % 5];
                $pivot[] = [
                    $custId,
                    $personId,
                    $relationship,
                    $time,
                    $time,
                ];
                $pidIndex++;
            }
        }

        // Insere pivot (PK composta evita duplicatas por par)
        $this->batchInsert('{{%customer_relationship_person}}', [
            'customer_id',
            'person_id',
            'relationship',
            'created_at',
            'updated_at'
        ], $pivot);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Remover somente os registros inseridos por esta migration (IDs usados)
        $customerIds = range(1001, 1060);
        $personIds = range(2001, 2120);

        // Deletar pivot primeiro (caso existam relações)
        $this->delete('{{%customer_relationship_person}}', ['customer_id' => $customerIds]);

        // Deletar people
        $this->delete('{{%person}}', ['id' => $personIds]);

        // Deletar customers
        $this->delete('{{%customer}}', ['id' => $customerIds]);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m251129_023453_seed_bulk_customers_people cannot be reverted.\n";

        return false;
    }
    */
}
