<?php

declare(strict_types=1);

namespace Chiarelli\DddApp\Domain\ValueObject;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;

final class Age
{
    private int $years;

    private function __construct(int $years)
    {
        $this->years = $years;
    }

    /**
     * Cria Age a partir de birthdate (string Y-m-d ou DateTimeInterface).
     * Opcionalmente aceita uma data de referência para testes determinísticos.
     */
    public static function fromBirthdate(string|DateTimeInterface $birthdate, ?DateTimeInterface $reference = null): self
    {
        // Se houver referência, vamos usar o timezone dela para normalizar ambas as datas
        $refTz = $reference ? $reference->getTimezone() : null;

        // 1) Normaliza birthdate para DateTimeImmutable como date-only no timezone apropriado.
        if (is_string($birthdate)) {
            // Strings representam datas (sem horário) — interpretamos no timezone da referência quando fornecida.
            $b = $refTz instanceof DateTimeZone
                ? DateTimeImmutable::createFromFormat('!Y-m-d', $birthdate, $refTz)
                : DateTimeImmutable::createFromFormat('!Y-m-d', $birthdate);
            if ($b === false) {
                throw new \InvalidArgumentException('Birthdate string must use Y-m-d format.');
            }
        } else {
            // Para DateTimeInterface, primeiro convertemos para DateTimeImmutable.
            if ($birthdate instanceof DateTimeImmutable) {
                $b = $birthdate;
            } else {
                $b = DateTimeImmutable::createFromFormat(
                    'Y-m-d H:i:s',
                    $birthdate->format('Y-m-d H:i:s'),
                    $birthdate->getTimezone()
                );
                if ($b === false) {
                    throw new \InvalidArgumentException('Unable to convert birthdate to DateTimeImmutable.');
                }
            }

            // Se houver referência, convertemos o instante do nascimento para o timezone da referência,
            // pois a comparação de "já fez aniversário neste ano?" deve considerar o mesmo instante.
            if ($refTz instanceof DateTimeZone) {
                $b = $b->setTimezone($refTz);
            }

            // Extrai somente a parte de data (meia-noite) no timezone atual (que será o da referência se foi fornecida).
            $b = DateTimeImmutable::createFromFormat('!Y-m-d', $b->format('Y-m-d'), $b->getTimezone());
            if ($b === false) {
                throw new \InvalidArgumentException('Unable to normalize birthdate to date-only.');
            }
        }

        // 2) Normaliza a referência para date-only no timezone escolhido (se houver referência, usa seu timezone).
        if ($reference instanceof DateTimeInterface) {
            $now = DateTimeImmutable::createFromFormat('!Y-m-d', $reference->format('Y-m-d'), $reference->getTimezone());
            if ($now === false) {
                // fallback razoável
                $now = new DateTimeImmutable('now', $b->getTimezone());
                $now = DateTimeImmutable::createFromFormat('!Y-m-d', $now->format('Y-m-d'), $now->getTimezone());
            }
        } else {
            // Se não há referência, usamos 'now' no timezone do birthdate (ou UTC se não disponível).
            $now = new DateTimeImmutable('now', $b->getTimezone());
            $now = DateTimeImmutable::createFromFormat('!Y-m-d', $now->format('Y-m-d'), $now->getTimezone());
        }

        // 3) Rejeita datas no futuro (comparando apenas datas, não horas).
        if ($b > $now) {
            throw new \InvalidArgumentException('Birthdate cannot be in the future.');
        }

        // 4) Calcula anos completos: diferença de anos menos 1 se ainda não fez aniversário no ano corrente.
        $years = (int)$now->format('Y') - (int)$b->format('Y');
        $nowMd = (int)$now->format('md');   // ex.: 0228
        $birthMd = (int)$b->format('md');   // ex.: 0229

        if ($nowMd < $birthMd) {
            $years--;
        }

        return new self($years);
    }

    public function value(): int
    {
        return $this->years;
    }

    public function __toString(): string
    {
        return (string)$this->years;
    }
}