<?php

/** @var yii\web\View $this */
/** @var array $entries */

use yii\helpers\Html;

$this->title = 'Customers & Linked People';
?>
<div class="customer-index mt-5">
    <h1><?= Html::encode($this->title) ?></h1>

    <?php if (empty($entries)): ?>
        <p class="text-muted">Nenhum customer encontrado.</p>
    <?php else: ?>
        <div class="row">
            <?php foreach ($entries as $entry):
                $customer = $entry->customer; // CustomerDto
                $people = $entry->people ?? []; // array of LinkedPersonDto
            ?>
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
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>