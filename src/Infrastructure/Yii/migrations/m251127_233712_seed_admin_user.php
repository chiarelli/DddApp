<?php

use yii\db\Migration;

class m251127_233712_seed_admin_user extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Evita duplicidade caso a migration seja aplicada num banco com admin já existente
        $exists = (new \yii\db\Query())
            ->from('{{%user}}')
            ->where(['username' => 'admin'])
            ->exists($this->db);

        if ($exists) {
            return;
        }

        $time = time();
        $this->insert('{{%user}}', [
            'nicename' => 'Admin',
            'fullname' => 'Administrator',
            'gender' => 'M',
            'birthdate' => null,
            'username' => 'admin',
            'auth_key' => Yii::$app->security->generateRandomString(),
            'password_hash' => Yii::$app->security->generatePasswordHash('admin'),
            'password_reset_token' => null,
            'verification_token' => null,
            'created_at' => $time,
            'updated_at' => $time,
            'created_by' => null,
            'updated_by' => null,
            'status' => 'Active',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->delete('{{%user}}', ['username' => 'admin']);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m251127_233712_seed_admin_user cannot be reverted.\n";

        return false;
    }
    */
}
