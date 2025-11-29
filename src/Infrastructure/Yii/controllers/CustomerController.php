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

        // Ler parâmetros de paginação/filtro da query string
        $page = (int)(Yii::$app->request->get('page', 1));
        $pageSize = (int)(Yii::$app->request->get('pageSize', 20));
        $q = Yii::$app->request->get('q', null);

        // Executa o caso de uso (retorna ['entries' => CustomerWithPeopleDto[], 'totalCount' => int, ...])
        $result = $useCase->execute($page, $pageSize, $q);

        $entries = $result['entries'] ?? [];
        $totalCount = $result['totalCount'] ?? 0;

        return $this->render('index', [
            'entries' => $entries,
            'totalCount' => $totalCount,
            'page' => $page,
            'pageSize' => $pageSize,
            'q' => $q,
        ]);
    }
}