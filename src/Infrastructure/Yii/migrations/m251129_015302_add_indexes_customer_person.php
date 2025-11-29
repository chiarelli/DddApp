<?php

use yii\db\Migration;

class m251129_015302_add_indexes_customer_person extends Migration
{
    public function safeUp()
    {
        // customer_relationship_person: composite (customer_id, person_id)
        $this->createIndex(
            'idx_crp_customer_person',
            '{{%customer_relationship_person}}',
            ['customer_id', 'person_id']
        );

        // customer_relationship_person: covering (customer_id, relationship, person_id)
        // NOTE: MySQL will maintain both indexes; covering index helps queries filtering by relationship.
        $this->createIndex(
            'idx_crp_c_rel_person',
            '{{%customer_relationship_person}}',
            ['customer_id', 'relationship', 'person_id']
        );

        // customer: index on fullname (useful for filters by name)
        $this->createIndex(
            'idx_customer_fullname',
            '{{%customer}}',
            ['fullname']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx_crp_c_rel_person', '{{%customer_relationship_person}}');
        $this->dropIndex('idx_crp_customer_person', '{{%customer_relationship_person}}');
        $this->dropIndex('idx_customer_fullname', '{{%customer}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m251129_015302_add_indexes_customer_person cannot be reverted.\n";

        return false;
    }
    */
}
