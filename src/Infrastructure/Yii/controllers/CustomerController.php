<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use Chiarelli\DddApp\Application\UseCase\ListCustomersWithPeopleQuery;

class CustomerController extends Controller
{
    /**
     * Lista customers com suas people vinculadas usando o UseCase via container.
     *
     * @return string
     */
    public function actionIndex()
    {
        /** @var ListCustomersWithPeopleQuery $useCase */
        $useCase = Yii::$container->get(ListCustomersWithPeopleQuery::class);

        // Executa o caso de uso (retorna CustomerWithPeopleDto[])
        $entries = $useCase->execute();

        return $this->render('index', [
            'entries' => $entries,
        ]);
    }
}
