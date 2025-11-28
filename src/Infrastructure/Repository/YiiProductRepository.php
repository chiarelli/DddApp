<?php

namespace Chiarelli\DddApp\Infrastructure\Repository;

use Chiarelli\DddApp\Domain\Repository\ProductRepositoryInterface;
use Chiarelli\DddApp\Domain\Entity\Product as DomainProduct;
use Chiarelli\DddApp\Domain\Entity\ProductType as DomainProductType;
use app\models\Product as ProductAR;

/**
 * Repositório que persiste Product via ActiveRecord do Yii.
 *
 * Observações:
 * - save() cria/atualiza um AR a partir da entidade de domínio.
 * - getLastSequenceForType() busca o registro com maior code e extrai os 4 dígitos finais,
 *   retornando null quando não houver registros para o tipo.
 * - Depende do bootstrap do Yii (autoloader e ActiveRecord).
 */
final class YiiProductRepository implements ProductRepositoryInterface
{
    public function save(DomainProduct $product): void
    {
        // Tenta atualizar por id (se fornecido) — caso contrário, busca por code
        $ar = null;
        $id = $product->getId();
        if ($id !== null) {
            $ar = ProductAR::findOne($id);
        }

        if ($ar === null && $product->getCode() !== '') {
            $ar = ProductAR::findOne(['code' => $product->getCode()]);
        }

        if ($ar === null) {
            $ar = new ProductAR();
        }

        // Mapear campos do domínio para AR (campos esperados conforme AR do Yii)
        $ar->type_id = $product->getType()->getId();
        $ar->name = $product->getName();
        $ar->description = $product->getDescription();
        $ar->price = $product->getPrice();
        $ar->code = $product->getCode();
        $ar->status = $product->getStatus();
        $ar->updated_at = time();
        if ($ar->isNewRecord) {
            $ar->created_at = $ar->created_at ?: time();
        }

        if (!$ar->save()) {
            // Em conformidade com guideline, não capturamos o erro aqui; deixamos subir para que
            // a aplicação trate (por exemplo, logs ou retry). Lançamos uma exceção genérica para deixar claro.
            $errors = $ar->getErrors();
            throw new \RuntimeException('Falha ao salvar Product AR: ' . json_encode($errors));
        }

        // Nota: não definimos o id na entidade de domínio (entidade não expõe setter de id).
    }

    public function getLastSequenceForType(DomainProductType $type): ?int
    {
        // Busca o produto com maior code para o tipo e extrai os 4 dígitos finais.
        $ar = ProductAR::find()
            ->where(['type_id' => $type->getId()])
            ->orderBy(['code' => SORT_DESC])
            ->limit(1)
            ->one();

        if ($ar === null || empty($ar->code)) {
            return null;
        }

        $code = (string)$ar->code;
        if (strlen($code) < 6) {
            return null;
        }

        $seqPart = substr($code, 2); // últimos 4 dígitos (string)
        // Remover zeros à esquerda e converter para int
        $seq = (int)ltrim($seqPart, '0');
        if ($seq === 0 && $seqPart !== '0000') {
            // caso incomum: seqPart tinha valor não numérico ou similar
            return null;
        }

        return $seq === 0 && $seqPart === '0000' ? 0 : $seq;
    }
}