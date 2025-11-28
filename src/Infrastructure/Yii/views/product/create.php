<?php
/** @var yii\web\View $this */
/** @var app\models\CreateProductForm $model */
/** @var array $types */
/** @var \app\models\Product[] $products */

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

    <!-- Lista de produtos criados -->
    <div class="product-list mt-5">
        <h2>Produtos Criados</h2>

        <?php if (empty($products)): ?>
            <p class="text-muted">Nenhum produto cadastrado ainda.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-bordered mt-3">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Código</th>
                            <th scope="col">Nome</th>
                            <th scope="col">Tipo</th>
                            <th scope="col">Preço</th>
                            <th scope="col">Status</th>
                            <th scope="col">Criado em</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $p): ?>
                            <tr>
                                <td><?= Html::encode((string)$p->code) ?></td>
                                <td><?= Html::encode((string)$p->name) ?></td>
                                <td><?= Html::encode($p->type->name ?? '-') ?></td>
                                <td><?= Html::encode(number_format((float)$p->price, 2, ',', '.')) ?></td>
                                <td><?= Html::encode((string)($p->status ?? '-')) ?></td>
                                <td><?= Html::encode($p->created_at ? date('Y-m-d H:i:s', (int)$p->created_at) : '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>