<?php

namespace app\models;

use yii\base\Model;

class CreateProductForm extends Model
{
  public string $name = '';
  public $price = null;
  public $product_type_id = null;

  public function rules()
  {
    return [
      [['name', 'price', 'product_type_id'], 'required'],
      ['name', 'string', 'max' => 245],
      ['price', 'number', 'min' => 0.01],
      ['product_type_id', 'integer'],
    ];
  }

  public function attributeLabels()
  {
    return [
      'name' => 'Nome',
      'price' => 'Preço',
      'product_type_id' => 'Tipo de Produto',
    ];
  }
}
