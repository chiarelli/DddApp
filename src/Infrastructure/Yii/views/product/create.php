<?php
/** @var yii\web\View $this */
/** @var app\models\CreateProductForm $model */
/** @var array $types */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

$this->title = 'Criação de Produtos';
?>
<div class="product-create mt-5">
    <h1><?= \yii\helpers\Html::encode($this->title) ?></h1>

    <div class="mt-4">
        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'price')->textInput(['type' => 'number', 'step' => '0.01']) ?>

        <?= $form->field($model, 'product_type_id')->dropDownList($types, ['prompt' => 'Selecione o tipo de produto']) ?>

        <div class="form-group mt-3">
            <?= Html::submitButton('Criar produto', ['class' => 'btn btn-primary']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>