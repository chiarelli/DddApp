<?php

namespace Chiarelli\DddApp\Infrastructure\Assembler;

use app\models\Person as PersonAR;
use Chiarelli\DddApp\Domain\Entity\Person as DomainPerson;
use DateTimeImmutable;

final class PersonAssembler
{
    /**
     * Map Person AR -> Domain Person
     *
     * @param PersonAR $ar
     * @return DomainPerson
     *
     * @throws \InvalidArgumentException if AR data invalid for domain invariants
     */
    public static function toDomain(PersonAR $ar): DomainPerson
    {
        $firstName = (string)$ar->first_name;
        $middle = $ar->middle_name ?? null;
        $last = $ar->last_name ?? null;
        $gender = $ar->gender ?? null;

        $birth = $ar->birthdate ?? '';
        if ($birth === '') {
            throw new \InvalidArgumentException('Person AR missing birthdate for id: ' . ($ar->id ?? 'unknown'));
        }

        $birthValue = $birth;
        if (is_string($birth) && preg_match('/^\d{4}-\d{2}-\d{2}( \d{2}:\d{2}:\d{2})?$/', $birth)) {
            if (strpos($birth, ' ') !== false) {
                $birthValue = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $birth) ?: $birth;
            } else {
                $birthValue = $birth;
            }
        }

        return new DomainPerson(
            $firstName,
            $birthValue,
            $middle,
            $last,
            $gender,
            $ar->id !== null ? (int)$ar->id : null
        );
    }
}