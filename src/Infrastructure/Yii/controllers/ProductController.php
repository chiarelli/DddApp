<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;

class ProductController extends Controller
{
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
     * Exibe a página de criação de produtos (apenas título por enquanto).
     *
     * @return string
     */
    public function actionCreate()
    {
        return $this->render('create');
    }
}