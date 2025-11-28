<?php

namespace Chiarelli\DddApp\Application\DTO;

final class CustomerDto
{
    public int $id;
    public string $fullName;
    public string $birthdate; // Y-m-d
    public int $age;

    public function __construct(int $id, string $fullName, string $birthdate, int $age)
    {
        $this->id = $id;
        $this->fullName = $fullName;
        $this->birthdate = $birthdate;
        $this->age = $age;
    }
}