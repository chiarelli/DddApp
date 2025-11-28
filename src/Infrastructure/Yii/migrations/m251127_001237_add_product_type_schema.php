<?php

use yii\db\Migration;

class m251127_001237_add_product_type_schema extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // 1 — Create product_type table (without normalized_name)
        $this->createTable('{{%product_type}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->notNull(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);

        // 2 — Add generated column normalized_name
        // LOWER(TRIM(name)) STORED
        $this->execute("
            ALTER TABLE {{%product_type}}
            ADD COLUMN normalized_name VARCHAR(255)
            GENERATED ALWAYS AS (LOWER(TRIM(name))) STORED;
        ");

        // 3 — Add unique index
        $this->createIndex(
            'idx_product_type_normalized_name',
            '{{%product_type}}',
            'normalized_name',
            true // unique
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%product_type}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m251127_001237_create_product_schema cannot be reverted.\n";

        return false;
    }
    */
}
