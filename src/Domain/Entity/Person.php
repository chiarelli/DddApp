<?php

declare(strict_types=1);

namespace Chiarelli\DddApp\Domain\Entity;

use Chiarelli\DddApp\Domain\ValueObject\Age;
use DateTimeImmutable;
use DateTimeInterface;

final class Person
{
    private ?int $id;
    private string $firstName;
    private ?string $middleName;
    private ?string $lastName;
    private DateTimeImmutable $birthdate;
    private ?string $gender;

    /**
     * @param string $firstName
     * @param string|DateTimeInterface $birthdate
     * @param string|null $middleName
     * @param string|null $lastName
     * @param string|null $gender
     * @param int|null $id
     */
    public function __construct(
        string $firstName,
        string|DateTimeInterface $birthdate,
        ?string $middleName = null,
        ?string $lastName = null,
        ?string $gender = null,
        ?int $id = null
    ) {
        $this->setFirstName($firstName);
        $this->setBirthdate($birthdate);
        $this->middleName = $middleName !== null ? trim($middleName) ?: null : null;
        $this->lastName = $lastName !== null ? trim($lastName) ?: null : null;
        $this->gender = $gender !== null ? trim($gender) ?: null : null;
        if ($id !== null && $id <= 0) {
            throw new \InvalidArgumentException('Person id must be positive when provided.');
        }
        $this->id = $id;
    }

    private function setFirstName(string $firstName): void
    {
        $trimmed = trim($firstName);
        if ($trimmed === '') {
            throw new \InvalidArgumentException('Person first name cannot be empty.');
        }
        $this->firstName = $trimmed;
    }

    /**
     * @param string|DateTimeInterface $birthdate
     */
    private function setBirthdate(string|DateTimeInterface $birthdate): void
    {
        if (is_string($birthdate)) {
            $dt = DateTimeImmutable::createFromFormat('Y-m-d', $birthdate);
            if ($dt === false) {
                throw new \InvalidArgumentException('Birthdate string must use Y-m-d format.');
            }
        } else {
            $dt = $birthdate instanceof DateTimeImmutable ? $birthdate : DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $birthdate->format('Y-m-d H:i:s'), $birthdate->getTimezone());
        }

        $now = new DateTimeImmutable('now');
        if ($dt > $now) {
            throw new \InvalidArgumentException('Person birthdate cannot be in the future.');
        }

        $this->birthdate = $dt;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getMiddleName(): ?string
    {
        return $this->middleName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function getBirthdate(): DateTimeImmutable
    {
        return $this->birthdate;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function getAge(?DateTimeInterface $reference = null): Age
    {
        return Age::fromBirthdate($this->birthdate, $reference);
    }
}