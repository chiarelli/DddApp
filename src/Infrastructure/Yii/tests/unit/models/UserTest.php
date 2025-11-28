<?php

namespace tests\unit\models;

use app\models\User;

class UserTest extends \Codeception\Test\Unit
{
    public function testFindUserById()
    {
        $admin = User::findByUsername('admin');
        verify($admin)->notEmpty();

        $byId = User::findIdentity((int)$admin->id);
        verify($byId)->notEmpty();
        verify($byId->username)->equals('admin');
    }

    public function testFindUserByAccessToken()
    {
        $this->expectException(\yii\base\NotSupportedException::class);
        User::findIdentityByAccessToken('any-token');
    }

    public function testFindUserByUsername()
    {
        verify($user = User::findByUsername('admin'))->notEmpty();
        verify(User::findByUsername('not-admin'))->empty();
    }

    /**
     * @depends testFindUserByUsername
     */
    public function testValidateUser()
    {
        $user = User::findByUsername('admin');
        // auth_key é gerado aleatoriamente no seed; valida com o próprio valor
        verify($user->validateAuthKey((string)$user->auth_key))->notEmpty();

        verify($user->validatePassword('admin'))->notEmpty();
        verify($user->validatePassword('123456'))->empty();
    }
}
