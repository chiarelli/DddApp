<?php

namespace Chiarelli\DddApp\Infrastructure\ReadModel;

use Chiarelli\DddApp\Application\DTO\CustomerDto;
use Chiarelli\DddApp\Application\DTO\LinkedPersonDto;
use Chiarelli\DddApp\Application\DTO\CustomerWithPeopleDto;
use Chiarelli\DddApp\Domain\ValueObject\Age;

/**
 * Converts joined rows (asArray) into CustomerWithPeopleDto[].
 *
 * Expected input rows (keys):
 *  - c_id, c_fullname, c_birthdate
 *  - p_id, p_first_name, p_middle_name, p_last_name, p_birthdate, p_gender
 *  - relationship
 */
final class ArrayToDtoMapper
{
    /**
     * @param array $rows
     * @param \DateTimeInterface|null $reference optional reference date for Age calculation
     * @return CustomerWithPeopleDto[]
     */
    public static function mapRowsToDtos(array $rows, ?\DateTimeInterface $reference = null): array
    {
        $map = [];

        foreach ($rows as $r) {
            $cid = isset($r['c_id']) ? (int)$r['c_id'] : null;
            if ($cid === null) {
                // skip malformed row
                continue;
            }

            if (!isset($map[$cid])) {
                $birth = $r['c_birthdate'] ?? null;
                // compute age (Age::fromBirthdate accepts string 'Y-m-d' or DateTimeInterface)
                $age = Age::fromBirthdate($birth, $reference)->value();

                $customerDto = new CustomerDto(
                    $cid,
                    (string)($r['c_fullname'] ?? ''),
                    (string)($birth ?? ''),
                    $age
                );

                $map[$cid] = [
                    'customer' => $customerDto,
                    'people' => [],
                ];
            }

            // If there's no person (left join), skip adding person
            if (empty($r['p_id'])) {
                continue;
            }

            $pid = (int)$r['p_id'];
            $pBirth = $r['p_birthdate'] ?? null;
            $personAge = Age::fromBirthdate($pBirth, $reference)->value();

            $personDto = new LinkedPersonDto(
                $pid,
                (string)($r['p_first_name'] ?? ''),
                isset($r['p_middle_name']) && $r['p_middle_name'] !== '' ? (string)$r['p_middle_name'] : null,
                isset($r['p_last_name']) && $r['p_last_name'] !== '' ? (string)$r['p_last_name'] : null,
                (string)($pBirth ?? ''),
                $personAge,
                isset($r['p_gender']) ? (string)$r['p_gender'] : null,
                (string)($r['relationship'] ?? '')
            );

            $map[$cid]['people'][] = [
                'person' => $personDto,
                'relationship' => (string)($r['relationship'] ?? ''),
            ];
        }

        // Preserve original ordering by customer fullname/id via insertion order of map built from ordered rows
        $result = [];
        foreach ($map as $entry) {
            $result[] = new CustomerWithPeopleDto($entry['customer'], $entry['people']);
        }

        return $result;
    }
}