<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use Chiarelli\DddApp\Application\UseCase\ListCustomersWithPeopleQuery;

class CustomerController extends Controller
{
    /**
     * Aplicar proteção: somente usuários autenticados podem acessar ações deste controller.
     *
     * @return array
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                // aplica a proteção para todas as ações deste controller
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'], // apenas usuários autenticados
                    ],
                ],
            ],
        ];
    }

    /**
     * Reforço: antes de qualquer ação, garante que usuário esteja autenticado.
     * Se for guest redireciona para a tela de login.
     *
     * @param \yii\base\Action $action
     * @return bool
     */
    public function beforeAction($action)
    {
        if (Yii::$app->user->isGuest) {
            $this->redirect(['site/login']);
            return false;
        }

        return parent::beforeAction($action);
    }

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
