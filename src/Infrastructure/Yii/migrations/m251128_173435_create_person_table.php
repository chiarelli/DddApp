<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%person}}`.
 */
class m251128_173435_create_person_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%person}}', [
            'id' => $this->primaryKey(),
            'first_name' => $this->string(100)->notNull(),
            'middle_name' => $this->string(100)->null(),
            'last_name' => $this->string(100)->null(),
            'birthdate' => $this->date()->notNull(),
            'gender' => $this->string(1)->null(),
            'created_at' => $this->integer()->null(),
            'updated_at' => $this->integer()->null(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%person}}');
    }
}
