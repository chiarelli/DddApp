<?php

namespace Chiarelli\DddApp\Application\Port;

/**
 * Abstração de provedor de cache para a camada de aplicação.
 */
interface CacheProviderInterface
{
    /**
     * Recupera do cache pelo $key ou, caso não exista, executa $producer,
     * armazena o resultado com o TTL informado e retorna o valor.
     *
     * @param string   $key      Chave de cache
     * @param int      $ttl      Tempo de vida em segundos (0 para infinito, conforme provider)
     * @param string[] $tags     Lista de tags para invalidação por TagDependency (opcional)
     * @param callable $producer Função que produz o valor quando não estiver em cache
     *
     * @return mixed Valor do cache (ou produzido)
     */
    public function remember(string $key, int $ttl, array $tags, callable $producer);

    /**
     * Variante de remember que permite ao producer retornar também as tags calculadas
     * dinamicalmente a partir do valor. O producer deve retornar um array com dois
     * elementos: [value, tagsArray].
     *
     * Isso evita executar a query duas vezes quando as tags só podem ser determinadas
     * a partir do resultado.
     *
     * @param string   $key
     * @param int      $ttl
     * @param callable $producer Callable que retorna [value, tagsArray]
     * @return mixed
     */
    public function rememberWithComputedTags(string $key, int $ttl, callable $producer);
}