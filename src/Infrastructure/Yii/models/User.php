<?php

namespace app\models;

use Yii;
use yii\base\NotSupportedException;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * User ActiveRecord para autenticação baseada em banco.
 *
 * Campos principais (ver migration de schema):
 * - id (int, PK)
 * - username (string, único)
 * - password_hash (string)
 * - auth_key (string)
 * - status (string|null)
 * - created_at, updated_at (int|null)
 */
class User extends ActiveRecord implements IdentityInterface
{
    public static function tableName()
    {
        return '{{%user}}';
    }

    // IdentityInterface
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    // Não utilizamos token de acesso neste exercício.
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('findIdentityByAccessToken is not implemented.');
    }

    public static function findByUsername(string $username): ?self
    {
        return static::findOne(['username' => $username]);
    }

    public function getId()
    {
        return $this->id;
    }

    public function getAuthKey(): ?string
    {
        return $this->auth_key;
    }

    public function validateAuthKey($authKey): bool
    {
        return $this->auth_key === $authKey;
    }

    public function validatePassword(string $password): bool
    {
        if (empty($this->password_hash)) {
            return false;
        }
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    // Helpers para administração/seed
    public function setPassword(string $password): void
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    public function generateAuthKey(): void
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    public function rules()
    {
        return [
            [['username', 'password_hash', 'auth_key', 'gender'], 'required'],
            [['username'], 'string', 'max' => 255],
            [['password_hash'], 'string', 'max' => 255],
            [['auth_key'], 'string', 'max' => 32],
            [['status'], 'string', 'max' => 255],
            [['username'], 'unique'],
            [['created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['nicename', 'fullname'], 'string', 'max' => 255],
            [['gender'], 'string', 'max' => 1],
            [['password_reset_token', 'verification_token'], 'string', 'max' => 255],
        ];
    }

    public function beforeSave($insert)
    {
        if ($insert && empty($this->auth_key)) {
            $this->generateAuthKey();
        }
        $this->updated_at = time();
        if ($insert && empty($this->created_at)) {
            $this->created_at = $this->updated_at;
        }
        return parent::beforeSave($insert);
    }
}