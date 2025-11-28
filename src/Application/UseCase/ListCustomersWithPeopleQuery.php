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
     * @param DateTimeInterface|null $reference Optional reference date to calculate ages deterministically in tests.
     * @return CustomerWithPeopleDto[]
     */
    public function execute(?DateTimeInterface $reference = null): array
    {
        $rows = $this->readRepository->findAllWithPeople();
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

        return $result;
    }
}