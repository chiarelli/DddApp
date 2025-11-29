<?php

/** @var yii\web\View $this */
/** @var array $entries */
/** @var int $totalCount */
/** @var int $page */
/** @var int $pageSize */
/** @var string|null $q */

use yii\helpers\Html;
use yii\data\Pagination;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\LinkPager;
use yii\caching\TagDependency;

$this->title = 'Customers & Linked People';
?>
<div class="customer-index mt-5">
    <h1><?= Html::encode($this->title) ?></h1>

    <div class="mb-3">
        <?php $form = ActiveForm::begin([
            'method' => 'get',
            'action' => ['customer/index'],
            'options' => ['class' => 'row g-2 align-items-center']
        ]); ?>
            <div class="col-auto">
                <input type="search" name="q" class="form-control" placeholder="Search by fullname" value="<?= Html::encode((string)($q ?? '')) ?>">
            </div>
            <div class="col-auto">
                <button class="btn btn-primary" type="submit">Search</button>
            </div>
            <div class="col-auto">
                <a href="<?= \yii\helpers\Url::to(['customer/index']) ?>" class="btn btn-outline-secondary">Reset</a>
            </div>
        <?php ActiveForm::end(); ?>
    </div>

    <?php
    $pagination = new Pagination([
        'totalCount' => (int)($totalCount ?? 0),
        'pageSize' => (int)($pageSize ?? 20),
    ]);
    if (!empty($page) && $page > 1) {
        $pagination->setPage(max(0, $page - 1));
    }

    $fragmentTtl = (int)(Yii::$app->params['customersList']['cacheTtl'] ?? 300);
    ?>

    <?php if ($pagination->getPageCount() > 1): ?>
        <nav aria-label="Page navigation" class="mb-3">
            <?= LinkPager::widget([
                'pagination' => $pagination,
                'options' => ['class' => 'pagination'],
            ]) ?>
        </nav>
    <?php endif; ?>

    <?php if (empty($entries)): ?>
        <p class="text-muted">Nenhum customer encontrado.</p>
    <?php else: ?>
        <div class="row">
            <?php foreach ($entries as $entry):
                $customer = $entry->customer;
                $people = $entry->people ?? [];
                $cid = (int)$customer->id;

                $cacheKey = 'customer_card_' . $cid;

                $dependency = new TagDependency([
                    'tags' => [
                        'customer_' . $cid,
                        'link_customer_' . $cid,
                    ],
                ]);

                if ($this->beginCache($cacheKey, ['dependency' => $dependency, 'duration' => $fragmentTtl])): ?>
                    <!-- customer_card_cached_at: <?= (string) (int) round(microtime(true) * 1000000) ?> :id=<?= Html::encode((string)$cid) ?> -->
                    <div class="col-12 mb-4">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h5 class="card-title mb-1"><?= Html::encode($customer->fullName) ?></h5>
                                        <p class="mb-0 text-muted">
                                            <strong>Birthdate:</strong> <?= Html::encode($customer->birthdate) ?>
                                            &nbsp;—&nbsp;
                                            <strong>Age:</strong> <?= Html::encode((string)$customer->age) ?>
                                        </p>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-primary">Customer #<?= Html::encode((string)$customer->id) ?></span>
                                    </div>
                                </div>

                                <hr class="my-3" />

                                <?php if (empty($people)): ?>
                                    <p class="text-muted mb-0">Nenhuma pessoa vinculada a este customer.</p>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-striped mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th scope="col">Primeiro Nome</th>
                                                    <th scope="col">Relação</th>
                                                    <th scope="col">Birthdate</th>
                                                    <th scope="col">Age</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($people as $p): ?>
                                                    <tr>
                                                        <td><?= Html::encode($p->firstName) ?></td>
                                                        <td><?= Html::encode($p->relationship) ?></td>
                                                        <td><?= Html::encode($p->birthdate) ?></td>
                                                        <td><?= Html::encode((string)$p->age) ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>

                            </div>
                        </div>
                    </div>
                    <?php $this->endCache(); ?>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($pagination->getPageCount() > 1): ?>
        <nav aria-label="Page navigation" class="mt-3">
            <?= LinkPager::widget([
                'pagination' => $pagination,
                'options' => ['class' => 'pagination'],
            ]) ?>
        </nav>
    <?php endif; ?>
</div>