<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\caching\TagDependency;

/**
 * This is the model class for table "customer_relationship_person".
 *
 * @property int $customer_id
 * @property int $person_id
 * @property string $relationship
 * @property int|null $created_at
 * @property int|null $updated_at
 *
 * @property Customer $customer
 * @property Person $person
 */
class CustomerRelationshipPerson extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'customer_relationship_person';
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
            [['created_at', 'updated_at'], 'default', 'value' => null],
            [['customer_id', 'person_id', 'relationship'], 'required'],
            [['customer_id', 'person_id', 'created_at', 'updated_at'], 'integer'],
            [['relationship'], 'string', 'max' => 50],
            [['customer_id', 'person_id'], 'unique', 'targetAttribute' => ['customer_id', 'person_id']],
            [['customer_id'], 'exist', 'skipOnError' => true, 'targetClass' => Customer::class, 'targetAttribute' => ['customer_id' => 'id']],
            [['person_id'], 'exist', 'skipOnError' => true, 'targetClass' => Person::class, 'targetAttribute' => ['person_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'customer_id' => 'Customer ID',
            'person_id' => 'Person ID',
            'relationship' => 'Relationship',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[Customer]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCustomer()
    {
        return $this->hasOne(Customer::class, ['id' => 'customer_id']);
    }

    /**
     * Gets query for [[Person]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPerson()
    {
        return $this->hasOne(Person::class, ['id' => 'person_id']);
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if (!Yii::$app->has('cache')) {
            return;
        }

        $tags = [];
        if ($this->customer_id) {
            $tags[] = 'link_customer_' . (int)$this->customer_id;
        }
        if ($this->person_id) {
            $tags[] = 'person_' . (int)$this->person_id;
        }

        if (!empty($tags)) {
            TagDependency::invalidate(Yii::$app->cache, $tags);
        }
    }

    public function afterDelete()
    {
        parent::afterDelete();

        if (!Yii::$app->has('cache')) {
            return;
        }

        $tags = [];
        if ($this->customer_id) {
            $tags[] = 'link_customer_' . (int)$this->customer_id;
        }
        if ($this->person_id) {
            $tags[] = 'person_' . (int)$this->person_id;
        }

        if (!empty($tags)) {
            TagDependency::invalidate(Yii::$app->cache, $tags);
        }
    }
}