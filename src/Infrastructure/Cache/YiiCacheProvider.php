<?php

namespace Chiarelli\DddApp\Infrastructure\Cache;

use Chiarelli\DddApp\Application\Port\CacheProviderInterface;
use yii\caching\TagDependency;

/**
 * Provedor de cache baseado no componente Yii::$app->cache.
 * Usa TagDependency para permitir invalidação por tags.
 */
final class YiiCacheProvider implements CacheProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function remember(string $key, int $ttl, array $tags, callable $producer)
    {
        $cache = \Yii::$app->cache ?? null;
        if ($cache === null) {
            return $producer();
        }

        $dependency = !empty($tags) ? new TagDependency(['tags' => $tags]) : null;

        return $cache->getOrSet(
            $key,
            static function () use ($producer) {
                return $producer();
            },
            $ttl,
            $dependency
        );
    }

    /**
     * {@inheritdoc}
     *
     * Producer must return [value, tagsArray].
     */
    public function rememberWithComputedTags(string $key, int $ttl, callable $producer)
    {
        $cache = \Yii::$app->cache ?? null;
        if ($cache === null) {
            $result = $producer();
            if (is_array($result) && count($result) === 2) {
                return $result[0];
            }
            return $result;
        }

        // If cached, return immediately
        $cached = $cache->get($key);
        if ($cached !== false) {
            return $cached;
        }

        // Not cached: execute producer which must return [value, tagsArray]
        $produced = $producer();

        if (!is_array($produced) || count($produced) !== 2) {
            // Fallback: if producer didn't return tags, store value without tags
            $value = $produced;
            $tags = [];
        } else {
            [$value, $tags] = $produced;
        }

        $dependency = !empty($tags) ? new TagDependency(['tags' => $tags]) : null;

        // Use set to allow specifying dependency
        $cache->set($key, $value, $ttl, $dependency);

        return $value;
    }
}