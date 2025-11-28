<?php

namespace Chiarelli\DddApp\Application\DTO;

final class LinkedPersonDto
{
    public int $id;
    public string $firstName;
    public ?string $middleName;
    public ?string $lastName;
    public string $birthdate; // Y-m-d
    public int $age;
    public ?string $gender;
    public string $relationship;

    public function __construct(
        int $id,
        string $firstName,
        ?string $middleName,
        ?string $lastName,
        string $birthdate,
        int $age,
        ?string $gender,
        string $relationship
    ) {
        $this->id = $id;
        $this->firstName = $firstName;
        $this->middleName = $middleName;
        $this->lastName = $lastName;
        $this->birthdate = $birthdate;
        $this->age = $age;
        $this->gender = $gender;
        $this->relationship = $relationship;
    }
}