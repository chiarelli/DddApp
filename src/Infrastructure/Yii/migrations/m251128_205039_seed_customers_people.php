<?php

use yii\db\Migration;

class m251128_205039_seed_customers_people extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $time = time();

        // 5 customers (inserindo ids explícitos para facilitar referencia nos seeds)
        $this->batchInsert('{{%customer}}', [
            'id',
            'gender',
            'fullname',
            'nicename',
            'birthdate',
            'status',
            'created_at',
            'updated_at'
        ], [
            [201, 'M', 'John Smith', 'John', '1975-04-10', 'Active', $time, $time],
            [202, 'F', 'Mary Johnson', 'Mary', '1980-09-25', 'Active', $time, $time],
            [203, 'M', 'Carlos Oliveira', 'Carlos', '1960-12-01', 'Active', $time, $time],
            [204, 'F', 'Ana Pereira', 'Ana', '1990-06-15', 'Active', $time, $time],
            [205, 'M', 'Luiz Santos', 'Luiz', '2000-01-05', 'Active', $time, $time],
        ]);

        // 10 people
        $this->batchInsert('{{%person}}', [
            'id',
            'first_name',
            'middle_name',
            'last_name',
            'birthdate',
            'gender',
            'created_at',
            'updated_at'
        ], [
            [301, 'Emma', null, 'Smith', '2001-03-20', 'F', $time, $time],
            [302, 'Lucas', null, 'Johnson', '1999-07-11', 'M', $time, $time],
            [303, 'Pedro', null, 'Oliveira', '1985-12-05', 'M', $time, $time],
            [304, 'Mariana', null, 'Pereira', '1970-02-28', 'F', $time, $time],
            [305, 'Roberto', null, 'Santos', '1950-10-10', 'M', $time, $time],
            [306, 'Carlos Jr', null, 'Oliveira', '1988-11-11', 'M', $time, $time],
            [307, 'Ana', null, 'Santos', '2003-05-05', 'F', $time, $time],
            [308, 'Gabriel', null, 'Silva', '1995-08-08', 'M', $time, $time],
            [309, 'Helena', null, 'Costa', '1978-01-01', 'F', $time, $time],
            [310, 'Joao', null, 'Pires', '2010-09-09', 'M', $time, $time],
        ]);

        // Relationships (pivot) — incl. múltiplas relações para a mesma pessoa
        $this->batchInsert('{{%customer_relationship_person}}', [
            'customer_id',
            'person_id',
            'relationship',
            'created_at',
            'updated_at'
        ], [
            // John Smith (201) relations
            [201, 301, 'daughter', $time, $time],       // Emma daughter of John
            [201, 302, 'son-in-law', $time, $time],    // Lucas son-in-law of John
            [201, 303, 'son', $time, $time],           // Pedro son of John

            // Mary Johnson (202) relations
            [202, 302, 'son', $time, $time],           // Lucas son of Mary
            [202, 307, 'granddaughter', $time, $time], // Ana granddaughter of Mary

            // Carlos Oliveira (203) relations
            [203, 303, 'son', $time, $time],           // Pedro son of Carlos
            [203, 306, 'son', $time, $time],           // Carlos Jr son of Carlos
            [203, 301, 'uncle_niece', $time, $time],   // Emma niece of Carlos (same person Emma linked to multiple customers)

            // Ana Pereira (204) relations
            [204, 304, 'mother', $time, $time],        // Mariana mother of Ana (example cross-link)
            [204, 310, 'grandson', $time, $time],      // Joao grandson of Ana

            // Luiz Santos (205) relations
            [205, 305, 'father', $time, $time],        // Roberto father of Luiz
            [205, 307, 'daughter', $time, $time],      // Ana daughter of Luiz (Ana linked also to Mary as granddaughter above)

            // Cross-links to create intrincacy:
            [201, 306, 'nephew', $time, $time],        // Carlos Jr nephew of John (same person connected multiple ways)
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Remove pivot rows for our seeded customers
        $this->delete('{{%customer_relationship_person}}', ['customer_id' => [201, 202, 203, 204, 205]]);

        // Remove people
        $this->delete('{{%person}}', ['id' => [301, 302, 303, 304, 305, 306, 307, 308, 309, 310]]);

        // Remove customers
        $this->delete('{{%customer}}', ['id' => [201, 202, 203, 204, 205]]);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m251128_205039_seed_customers_people cannot be reverted.\n";

        return false;
    }
    */
}
