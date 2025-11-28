<?php

declare(strict_types=1);

namespace Chiarelli\DddApp\Domain\Entity;

use Chiarelli\DddApp\Domain\ValueObject\Age;
use DateTimeImmutable;
use DateTimeInterface;

final class Customer
{
    private ?int $id;
    private string $fullName;
    private DateTimeImmutable $birthdate;

    public function __construct(string $fullName, string|DateTimeInterface $birthdate, ?int $id = null)
    {
        $this->setFullName($fullName);
        $this->setBirthdate($birthdate);
        if ($id !== null && $id <= 0) {
            throw new \InvalidArgumentException('Customer id must be positive when provided.');
        }
        $this->id = $id;
    }

    private function setFullName(string $fullName): void
    {
        $trimmed = trim($fullName);
        if ($trimmed === '') {
            throw new \InvalidArgumentException('Customer full name cannot be empty.');
        }
        $this->fullName = $trimmed;
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
            throw new \InvalidArgumentException('Customer birthdate cannot be in the future.');
        }

        $this->birthdate = $dt;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFullName(): string
    {
        return $this->fullName;
    }

    public function getBirthdate(): DateTimeImmutable
    {
        return $this->birthdate;
    }

    /**
     * Retorna Age; aceita referência opcional para testar cenários determinísticos.
     */
    public function getAge(?DateTimeInterface $reference = null): Age
    {
        return Age::fromBirthdate($this->birthdate, $reference);
    }
}