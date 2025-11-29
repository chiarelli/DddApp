<?php

use Chiarelli\DddApp\Application\Port\CacheProviderInterface;
use Chiarelli\DddApp\Application\UseCase\ListCustomersWithPeopleQuery;
use Chiarelli\DddApp\Domain\Repository\CustomerReadRepositoryInterface;
use Chiarelli\DddApp\Infrastructure\Repository\YiiCustomerReadRepository;

class CustomerCacheCest
{
    public function _before(\FunctionalTester $I)
    {
        // Garantir que o usuário admin exista e esteja logado (caso a lista exija auth).
        $admin = \app\models\User::findByUsername('admin');
        $I->assertNotNull($admin, 'Admin user must exist for functional tests');
        $I->amLoggedInAs((int)$admin->id);
    }

    /**
     * Verifica que o segundo execute() reutiliza o cache e não refaz consultas ao repositório.
     */
    public function cacheReducesRepositoryCalls(\FunctionalTester $I)
    {
        // Ativa cache na aplicação
        putenv('CUSTOMERS_LIST_CACHE_ENABLED=true');
        putenv('CUSTOMERS_LIST_CACHE_TTL=60');
        putenv('CUSTOMERS_LIST_PAGE_SIZE_DEFAULT=20');

        // Wrapper que conta chamadas no repositório real
        $realRepo = new YiiCustomerReadRepository();
        $wrapper = new class($realRepo) implements CustomerReadRepositoryInterface {
            private $inner;
            public int $countCalls = 0;
            public int $findCalls = 0;
            public function __construct($inner)
            {
                $this->inner = $inner;
            }
            public function findAllWithPeople(): array
            {
                $this->countCalls++;
                return $this->inner->findAllWithPeople();
            }
            public function findAllWithPeoplePaginated(array $filters, int $page, int $pageSize): array
            {
                $this->findCalls++;
                return $this->inner->findAllWithPeoplePaginated($filters, $page, $pageSize);
            }
            public function countAll(array $filters): int
            {
                $this->countCalls++;
                return $this->inner->countAll($filters);
            }
        };

        // Provider em memória para controlar o cache durante o teste
        $memoryCache = new class implements CacheProviderInterface {
            public array $store = [];
            public array $tags = [];
            public function remember(string $key, int $ttl, array $tags, callable $producer)
            {
                if (array_key_exists($key, $this->store)) {
                    return $this->store[$key];
                }
                $value = $producer();
                $this->store[$key] = $value;
                $this->tags[$key] = $tags;
                return $value;
            }
            public function rememberWithComputedTags(string $key, int $ttl, callable $producer)
            {
                if (array_key_exists($key, $this->store)) {
                    return $this->store[$key];
                }
                $result = $producer();
                if (is_array($result) && count($result) === 2) {
                    [$value, $tags] = $result;
                } else {
                    $value = $result;
                    $tags = [];
                }
                $this->store[$key] = $value;
                $this->tags[$key] = $tags;
                return $value;
            }
        };

        // Ajusta o container para usar o wrapper e o provider em memória
        \Yii::$container->set(CustomerReadRepositoryInterface::class, function () use ($wrapper) {
            return $wrapper;
        });
        \Yii::$container->set(CacheProviderInterface::class, function () use ($memoryCache) {
            return $memoryCache;
        });
        \Yii::$container->set(ListCustomersWithPeopleQuery::class, function () use ($wrapper, $memoryCache) {
            return new ListCustomersWithPeopleQuery($wrapper, $memoryCache);
        });

        /** @var ListCustomersWithPeopleQuery $useCase */
        $useCase = \Yii::$container->get(ListCustomersWithPeopleQuery::class);

        // Primeira execução → cache miss (consulta repo)
        $resultFirst = $useCase->execute(1, 20, null);
        $I->assertArrayHasKey('entries', $resultFirst);
        $I->assertGreaterThan(0, $wrapper->countCalls + $wrapper->findCalls, 'Repository should have been called on first execution');
        $I->assertNotEmpty($memoryCache->store, 'Cache store should contain the cached value');
        $callsAfterFirst = $wrapper->countCalls + $wrapper->findCalls;

        // Segunda execução → cache hit (contadores não devem aumentar)
        $resultSecond = $useCase->execute(1, 20, null);
        $callsAfterSecond = $wrapper->countCalls + $wrapper->findCalls;

        $I->assertSame($callsAfterFirst, $callsAfterSecond, 'Repository call counts should not increase on second execution (cache hit)');
        $I->assertSame($resultFirst['entries'], $resultSecond['entries'], 'Cached entries should match the first execution.');
    }
}
