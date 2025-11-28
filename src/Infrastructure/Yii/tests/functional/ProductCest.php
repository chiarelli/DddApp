<?php

class ProductCest
{
    private ?int $typeId = null;

    public function _before(\FunctionalTester $I)
    {
        // Cria um ProductType mínimo para o teste
        $type = new \app\models\ProductType();
        $type->name = 'FunctionalTestType';
        $type->save(false);
        $this->typeId = (int)$type->id;

        // Faz login usando o AR do usuário (admin seed existe nas migrations)
        $I->amLoggedInAs(\app\models\User::findByUsername('admin'));
    }

    public function testCreateProductSuccessfully(\FunctionalTester $I)
    {
        $I->amOnRoute('product/create');
        $I->see('Criação de Produtos', 'h1');

        $I->submitForm('#create-product-form', [
            'CreateProductForm[name]' => 'Functional Product',
            'CreateProductForm[price]' => '19.90',
            'CreateProductForm[product_type_id]' => (string)$this->typeId,
        ]);

        // Mensagem de sucesso e presença do item na listagem
        $I->see('Produto criado com sucesso');
        $I->see('Functional Product');
    }

    public function testCreateProductValidationErrors(\FunctionalTester $I)
    {
        $I->amOnRoute('product/create');

        // Submete formulário vazio e espera por mensagens de validação
        $I->submitForm('#create-product-form', [
            'CreateProductForm[name]' => '',
            'CreateProductForm[price]' => '',
            'CreateProductForm[product_type_id]' => '',
        ]);

        // Mensagens de validação padrão (verifica fragmento 'cannot be blank')
        $I->see('cannot be blank');
    }
}
