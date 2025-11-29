<?php

namespace Chiarelli\DddApp\Application\UseCase;

use Chiarelli\DddApp\Application\DTO\CustomerDto;
use Chiarelli\DddApp\Application\DTO\CustomerWithPeopleDto;
use Chiarelli\DddApp\Application\DTO\LinkedPersonDto;
use Chiarelli\DddApp\Domain\Repository\CustomerReadRepositoryInterface;
use Chiarelli\DddApp\Domain\ValueObject\Age;
use DateTimeInterface;

final class ListCustomersWithPeopleQuery
{
    private CustomerReadRepositoryInterface $readRepository;

    public function __construct(CustomerReadRepositoryInterface $readRepository)
    {
        $this->readRepository = $readRepository;
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
            $pageSize = 20;
            $q = null;
        } else {
            $page = is_int($pageOrReference) && $pageOrReference > 0 ? $pageOrReference : 1;
        }

        $filters = [];
        if ($q !== null && trim($q) !== '') {
            $filters['q'] = trim($q);
        }

        // total count for pagination
        $total = $this->readRepository->countAll($filters);

        // fetch paginated rows from repository
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