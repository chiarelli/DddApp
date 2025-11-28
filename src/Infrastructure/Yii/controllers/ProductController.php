<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use app\models\ProductType as ProductTypeAR;
use app\models\Product as ProductAR;
use app\models\CreateProductForm;
use Chiarelli\DddApp\Application\UseCase\CreateProductUseCase;
use Chiarelli\DddApp\Application\DTO\CreateProductRequest;

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
     * Exibe e processa o formulário de criação de produtos (ainda simples).
     *
     * Também passa a lista de produtos existente para a view para exibição em tabela.
     *
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new CreateProductForm();

        // Carrega lista de tipos para dropdown
        $types = ArrayHelper::map(ProductTypeAR::find()->orderBy(['name' => SORT_ASC])->all(), 'id', 'name');

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            // Obter o UseCase via container para respeitar injeção de dependências
            /** @var CreateProductUseCase $useCase */
            $useCase = Yii::$container->get(CreateProductUseCase::class);

            // Preparar DTO de requisição
            $requestDto = new CreateProductRequest(
                $model->name,
                (float)$model->price,
                (int)$model->product_type_id
            );

            // Executa o caso de uso (pode lançar InvalidArgumentException em regras de domínio)
            $response = $useCase->execute($requestDto);

            // Informar sucesso e código gerado
            Yii::$app->session->setFlash('success', "Produto criado com sucesso. Código: {$response->code}");

            // Redireciona para a mesma página para limpar POST (PRG)
            return $this->refresh();
        }

        // Buscar produtos existentes (sem paginação), ordenando do mais recente ao mais antigo
        $products = ProductAR::find()->with('type')->orderBy(['created_at' => SORT_DESC])->all();

        return $this->render('create', [
            'model' => $model,
            'types' => $types,
            'products' => $products,
        ]);
    }
}
