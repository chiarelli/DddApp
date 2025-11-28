<?php

namespace Chiarelli\DddApp\Application\DTO;

final class CustomerWithPeopleDto
{
    public CustomerDto $customer;
    /** @var LinkedPersonDto[] */
    public array $people;

    /**
     * @param CustomerDto $customer
     * @param LinkedPersonDto[] $people
     */
    public function __construct(CustomerDto $customer, array $people)
    {
        $this->customer = $customer;
        $this->people = $people;
    }
}