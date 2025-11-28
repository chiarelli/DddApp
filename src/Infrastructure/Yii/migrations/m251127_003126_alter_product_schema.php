<?php

use yii\db\Migration;

class m251127_003126_alter_product_schema extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%product}}', 'price', $this->decimal(10, 2)->notNull());
        $this->addColumn('{{%product}}', 'code', $this->string(20)->unique()->notNull());

        // fix: make type_id NOT NULL
        $this->alterColumn('{{%product}}', 'type_id', $this->integer()->notNull());

        // 3 — Add FK
        $this->addForeignKey(
            'fk_product_type',
            '{{%product}}',
            'type_id',
            '{{%product_type}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // 4 — Seed
        $this->batchInsert('{{%product_type}}', ['name'], [
            ['Dress'],
            ['Skirt'],
            ['T-Shirt'],
            ['Shoes'],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_product_type', '{{%product}}');
        $this->dropColumn('{{%product}}', 'code');
        $this->dropColumn('{{%product}}', 'price');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m251127_003126_alter_product_schema cannot be reverted.\n";

        return false;
    }
    */
}
