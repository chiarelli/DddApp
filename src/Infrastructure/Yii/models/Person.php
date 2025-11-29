<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\caching\TagDependency;

/**
 * This is the model class for table "person".
 *
 * @property int $id
 * @property string $first_name
 * @property string|null $middle_name
 * @property string|null $last_name
 * @property string $birthdate
 * @property string|null $gender
 * @property int|null $created_at
 * @property int|null $updated_at
 *
 * @property CustomerRelationshipPerson[] $customerRelationshipPeople
 * @property Customer[] $customers
 */
class Person extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'person';
    }

    /**
     * Behaviors to auto-fill timestamps.
     *
     * @return array
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    \yii\db\ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    \yii\db\ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
                'value' => function () {
                    return time();
                },
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['middle_name', 'last_name', 'gender', 'created_at', 'updated_at'], 'default', 'value' => null],
            [['first_name', 'birthdate'], 'required'],
            [['birthdate'], 'safe'],
            [['created_at', 'updated_at'], 'integer'],
            [['first_name', 'middle_name', 'last_name'], 'string', 'max' => 100],
            [['gender'], 'string', 'max' => 1],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'first_name' => 'First Name',
            'middle_name' => 'Middle Name',
            'last_name' => 'Last Name',
            'birthdate' => 'Birthdate',
            'gender' => 'Gender',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[CustomerRelationshipPeople]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCustomerRelationshipPeople()
    {
        return $this->hasMany(CustomerRelationshipPerson::class, ['person_id' => 'id']);
    }

    /**
     * Gets query for [[Customers]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCustomers()
    {
        return $this->hasMany(Customer::class, ['id' => 'customer_id'])->viaTable('customer_relationship_person', ['person_id' => 'id']);
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if (!Yii::$app->has('cache') || !$this->id) {
            return;
        }

        $tags = ['person_' . (int)$this->id];

        // Descobre customers impactados e adiciona tags de link_customer
        $customerIds = (new \yii\db\Query())
            ->from('{{%customer_relationship_person}}')
            ->select('customer_id')
            ->where(['person_id' => (int)$this->id])
            ->column();

        foreach ($customerIds as $cid) {
            $tags[] = 'link_customer_' . (int)$cid;
        }

        TagDependency::invalidate(Yii::$app->cache, array_values(array_unique($tags)));
    }

    public function afterDelete()
    {
        parent::afterDelete();

        if (!Yii::$app->has('cache') || !$this->id) {
            return;
        }

        $tags = ['person_' . (int)$this->id];

        $customerIds = (new \yii\db\Query())
            ->from('{{%customer_relationship_person}}')
            ->select('customer_id')
            ->where(['person_id' => (int)$this->id])
            ->column();

        foreach ($customerIds as $cid) {
            $tags[] = 'link_customer_' . (int)$cid;
        }

        TagDependency::invalidate(Yii::$app->cache, array_values(array_unique($tags)));
    }
}