<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "product_type".
 *
 * @property int $id
 * @property string $name
 * @property int|null $created_at
 * @property int|null $updated_at
 * @property string|null $normalized_name
 *
 * @property Product[] $products
 */
class ProductType extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'product_type';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at', 'normalized_name'], 'default', 'value' => null],
            [['name'], 'required'],
            [['created_at', 'updated_at'], 'integer'],
            [['name', 'normalized_name'], 'string', 'max' => 255],
            [['normalized_name'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'normalized_name' => 'Normalized Name',
        ];
    }

    /**
     * Gets query for [[Products]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProducts()
    {
        return $this->hasMany(Product::class, ['type_id' => 'id']);
    }

}
