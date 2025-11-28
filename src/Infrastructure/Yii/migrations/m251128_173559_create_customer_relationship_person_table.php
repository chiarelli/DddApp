<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%customer_relationship_person}}`.
 */
class m251128_173559_create_customer_relationship_person_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%customer_relationship_person}}', [
            'customer_id' => $this->integer()->notNull(),
            'person_id' => $this->integer()->notNull(),
            'relationship' => $this->string(50)->notNull(),
            'created_at' => $this->integer()->null(),
            'updated_at' => $this->integer()->null(),
        ]);

        // Define PK composta (customer_id, person_id)
        $this->addPrimaryKey(
            'pk-customer_person',
            '{{%customer_relationship_person}}',
            ['customer_id', 'person_id']
        );

        // Foreign key para customer(id)
        $this->addForeignKey(
            'fk-customer_person-customer',
            '{{%customer_relationship_person}}',
            'customer_id',
            '{{%customer}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // Foreign key para person(id)
        $this->addForeignKey(
            'fk-customer_person-person',
            '{{%customer_relationship_person}}',
            'person_id',
            '{{%person}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Remover FKs e PK antes de dropar a tabela
        $this->dropForeignKey('fk-customer_person-customer', '{{%customer_relationship_person}}');
        $this->dropForeignKey('fk-customer_person-person', '{{%customer_relationship_person}}');
        $this->dropPrimaryKey('pk-customer_person', '{{%customer_relationship_person}}');

        $this->dropTable('{{%customer_relationship_person}}');
    }
}
