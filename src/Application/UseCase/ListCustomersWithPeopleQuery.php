<?php

namespace Chiarelli\DddApp\Application\UseCase;

use Chiarelli\DddApp\Application\DTO\CustomerDto;
use Chiarelli\DddApp\Application\DTO\CustomerWithPeopleDto;
use Chiarelli\DddApp\Application\DTO\LinkedPersonDto;
use Chiarelli\DddApp\Domain\Repository\CustomerReadRepositoryInterface;
use Chiarelli\DddApp\Domain\ValueObject\Age;
use Chiarelli\DddApp\Application\Port\CacheProviderInterface;
use DateTimeInterface;

final class ListCustomersWithPeopleQuery
{
    private CustomerReadRepositoryInterface $readRepository;
    private ?CacheProviderInterface $cacheProvider;
    private bool $cacheEnabled;
    private int $cacheTtl;
    private int $pageSizeDefault;

    public function __construct(CustomerReadRepositoryInterface $readRepository, ?CacheProviderInterface $cacheProvider = null)
    {
        $this->readRepository = $readRepository;
        $this->cacheProvider = $cacheProvider;

        // Read settings from environment with safe defaults
        $this->cacheEnabled = filter_var(getenv('CUSTOMERS_LIST_CACHE_ENABLED') ?: 'false', FILTER_VALIDATE_BOOLEAN);
        $this->cacheTtl = (int) (getenv('CUSTOMERS_LIST_CACHE_TTL') ?: 300);
        $this->pageSizeDefault = (int) (getenv('CUSTOMERS_LIST_PAGE_SIZE_DEFAULT') ?: 20);
    }

    /**
     * Executa a query de listagem.
     *
     * Parâmetros:
     *  - $page (1-based) e $pageSize definem paginação.
     *  - $q é filtro por fullname (LIKE).
     *  - $reference é opcional para calcular idades de forma determinística (tests).
     *
     * Retorna um array com chaves:
     *  - 'entries' => CustomerWithPeopleDto[]
     *  - 'totalCount' => int
     *  - 'page' => int
     *  - 'pageSize' => int
     *
     * Compatibilidade:
     *  - se chamado passando apenas DateTimeInterface como primeiro arg (estilo antigo),
     *    a função detecta e age como referência sem paginação (page=1,pageSize=20).
     *
     * @param int|DateTimeInterface|null $pageOrReference
     * @param int $pageSize
     * @param string|null $q
     * @param DateTimeInterface|null $reference
     * @return array
     */
    public function execute($pageOrReference = null, int $pageSize = 20, ?string $q = null, ?DateTimeInterface $reference = null): array
    {
        // Backwards compatibility: caller might pass DateTimeInterface as first param.
        if ($pageOrReference instanceof DateTimeInterface) {
            $reference = $pageOrReference;
            $page = 1;
            $pageSize = $this->pageSizeDefault;
            $q = null;
        } else {
            $page = is_int($pageOrReference) && $pageOrReference > 0 ? $pageOrReference : 1;
        }

        // normalize pageSize
        $pageSize = $pageSize > 0 ? $pageSize : $this->pageSizeDefault;

        $filters = [];
        if ($q !== null && trim($q) !== '') {
            $filters['q'] = trim($q);
        }

        // If cache is enabled and provider is available, try cache-aside
        if ($this->cacheEnabled && $this->cacheProvider !== null) {
            $filtersHash = md5(json_encode($filters));
            $cacheKey = sprintf('customers:list:%s:p=%d:s=%d', $filtersHash, $page, $pageSize);

            // Use rememberWithComputedTags: producer must return [value, tagsArray]
            $cached = $this->cacheProvider->rememberWithComputedTags($cacheKey, $this->cacheTtl, function () use ($filters, $page, $pageSize, $reference) {
                // Compute fresh result
                $total = $this->readRepository->countAll($filters);
                $rows = $this->readRepository->findAllWithPeoplePaginated($filters, $page, $pageSize);

                // Map rows to DTOs (same logic as before)
                $resultEntries = [];
                $tags = [];

                foreach ($rows as $row) {
                    /** @var \Chiarelli\DddApp\Domain\Entity\Customer $customer */
                    $customer = $row['customer'];
                    /** @var array $peoplePairs */
                    $peoplePairs = $row['people'] ?? [];

                    $custBirth = $customer->getBirthdate();
                    $custAge = Age::fromBirthdate($custBirth, $reference)->value();
                    $customerDto = new CustomerDto(
                        (int)$customer->getId(),
                        $customer->getFullName(),
                        $custBirth->format('Y-m-d'),
                        $custAge
                    );

                    $peopleDtos = [];
                    foreach ($peoplePairs as $pair) {
                        $person = $pair['person'];
                        $relationship = (string)($pair['relationship'] ?? '');

                        $personBirth = $person->getBirthdate();
                        $personAge = Age::fromBirthdate($personBirth, $reference)->value();

                        $peopleDtos[] = new LinkedPersonDto(
                            (int)$person->getId(),
                            $person->getFirstName(),
                            $person->getMiddleName(),
                            $person->getLastName(),
                            $personBirth->format('Y-m-d'),
                            $personAge,
                            $person->getGender(),
                            $relationship
                        );

                        // add person tag
                        if ($person->getId() !== null) {
                            $tags[] = 'person_' . (int)$person->getId();
                        }
                    }

                    // add customer tags
                    if ($customer->getId() !== null) {
                        $cid = (int)$customer->getId();
                        $tags[] = 'customer_' . $cid;
                        $tags[] = 'link_customer_' . $cid;
                    }

                    $resultEntries[] = new CustomerWithPeopleDto($customerDto, $peopleDtos);
                }

                // ensure tags unique
                $tags = array_values(array_unique($tags));

                $value = [
                    'entries' => $resultEntries,
                    'totalCount' => $total,
                    'page' => $page,
                    'pageSize' => $pageSize,
                ];

                return [$value, $tags];
            });

            return $cached;
        }

        // No cache path: behave as original implementation
        $total = $this->readRepository->countAll($filters);

        $rows = $this->readRepository->findAllWithPeoplePaginated($filters, $page, $pageSize);

        $result = [];

        foreach ($rows as $row) {
            /** @var \Chiarelli\DddApp\Domain\Entity\Customer $customer */
            $customer = $row['customer'];
            /** @var array $peoplePairs */
            $peoplePairs = $row['people'] ?? [];

            $custBirth = $customer->getBirthdate();
            $custAge = Age::fromBirthdate($custBirth, $reference)->value();
            $customerDto = new CustomerDto(
                (int)$customer->getId(),
                $customer->getFullName(),
                $custBirth->format('Y-m-d'),
                $custAge
            );

            $peopleDtos = [];
            foreach ($peoplePairs as $pair) {
                $person = $pair['person'];
                $relationship = (string)($pair['relationship'] ?? '');

                $personBirth = $person->getBirthdate();
                $personAge = Age::fromBirthdate($personBirth, $reference)->value();

                $peopleDtos[] = new LinkedPersonDto(
                    (int)$person->getId(),
                    $person->getFirstName(),
                    $person->getMiddleName(),
                    $person->getLastName(),
                    $personBirth->format('Y-m-d'),
                    $personAge,
                    $person->getGender(),
                    $relationship
                );
            }

            $result[] = new CustomerWithPeopleDto($customerDto, $peopleDtos);
        }

        return [
            'entries' => $result,
            'totalCount' => $total,
            'page' => $page,
            'pageSize' => $pageSize,
        ];
    }
}