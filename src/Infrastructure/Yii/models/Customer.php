<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "customer".
 *
 * @property int $id
 * @property string|null $gender
 * @property string|null $fullname
 * @property string|null $nicename
 * @property string|null $birthdate
 * @property string $status
 * @property int|null $created_at
 * @property int|null $updated_at
 * @property int|null $created_by
 * @property int|null $updated_by
 *
 * @property CustomerRelationshipPerson[] $customerRelationshipPeople
 * @property Person[] $people
 */
class Customer extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'customer';
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
            [['gender', 'fullname', 'nicename', 'birthdate', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'default', 'value' => null],
            [['status'], 'default', 'value' => 'Active'],
            [['birthdate'], 'safe'],
            [['created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['gender'], 'string', 'max' => 1],
            [['fullname', 'nicename', 'status'], 'string', 'max' => 145],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'gender' => 'Gender',
            'fullname' => 'Fullname',
            'nicename' => 'Nicename',
            'birthdate' => 'Birthdate',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * Gets query for [[CustomerRelationshipPeople]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCustomerRelationshipPeople()
    {
        return $this->hasMany(CustomerRelationshipPerson::class, ['customer_id' => 'id']);
    }

    /**
     * Gets query for [[People]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPeople()
    {
        return $this->hasMany(Person::class, ['id' => 'person_id'])->viaTable('customer_relationship_person', ['customer_id' => 'id']);
    }

}