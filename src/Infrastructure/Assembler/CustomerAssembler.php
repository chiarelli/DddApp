<?php

namespace Chiarelli\DddApp\Infrastructure\Assembler;

use app\models\Customer as CustomerAR;
use Chiarelli\DddApp\Domain\Entity\Customer as DomainCustomer;
use DateTimeImmutable;

final class CustomerAssembler
{
    /**
     * Map Customer AR -> Domain Customer
     *
     * @param CustomerAR $ar
     * @return DomainCustomer
     *
     * @throws \InvalidArgumentException if AR data invalid for domain invariants
     */
    public static function toDomain(CustomerAR $ar): DomainCustomer
    {
        // prefer fullname, fallback to nicename when available
        $fullName = (string)($ar->fullname ?? $ar->nicename ?? '');

        // birthdate in AR is expected as string 'Y-m-d' or null; domain requires a valid date
        $birth = $ar->birthdate ?? '';
        // let the Domain constructor validate formats / invariants; pass DateTimeImmutable when possible
        if ($birth === '') {
            throw new \InvalidArgumentException('Customer AR missing birthdate for id: ' . ($ar->id ?? 'unknown'));
        }

        // If AR birthdate already contains time (unlikely) we still pass the string or a DateTimeImmutable
        $birthValue = $birth;
        // Attempt to create DateTimeImmutable for safety if possible
        if (is_string($birth) && preg_match('/^\d{4}-\d{2}-\d{2}( \d{2}:\d{2}:\d{2})?$/', $birth)) {
            // If contains time, create from the full format, otherwise from Y-m-d
            if (strpos($birth, ' ') !== false) {
                $birthValue = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $birth) ?: $birth;
            } else {
                $birthValue = $birth;
            }
        }

        return new DomainCustomer($fullName, $birthValue, $ar->id !== null ? (int)$ar->id : null);
    }
}