<?php

namespace Chiarelli\DddApp\Domain\Entity;

final class Product
{
  public const STATUS_ACTIVE = 'Active';
  public const STATUS_INACTIVE = 'Inactive';

  private ?int $id;
  private ProductType $type;
  private string $name;
  private float $price;
  private string $code;
  private string $status;
  private ?string $description;

  public function __construct(
    ProductType $type,
    string $name,
    float $price,
    string $code = '',
    string $status = self::STATUS_ACTIVE,
    ?string $description = null,
    ?int $id = null
  ) {
    $this->type = $type;
    $this->setName($name);
    $this->setPrice($price);
    $this->setStatus($status);
    $this->description = $description;
    $this->id = $id;
    $this->code = '';

    if ($code !== '') {
      $this->setCode($code);
    }
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  public function getType(): ProductType
  {
    return $this->type;
  }

  public function getName(): string
  {
    return $this->name;
  }

  public function getPrice(): float
  {
    return $this->price;
  }

  public function getCode(): string
  {
    return $this->code;
  }

  public function getStatus(): string
  {
    return $this->status;
  }

  public function getDescription(): ?string
  {
    return $this->description;
  }

  public function setName(string $name): void
  {
    $trimmed = trim($name);
    if ($trimmed === '') {
      throw new \InvalidArgumentException('Product name cannot be empty.');
    }
    $this->name = $trimmed;
  }

  public function setPrice(float $price): void
  {
    if ($price <= 0) {
      throw new \InvalidArgumentException('Product price must be positive.');
    }
    $this->price = $price;
  }

  public function setStatus(string $status): void
  {
    $allowed = [self::STATUS_ACTIVE, self::STATUS_INACTIVE];
    if (!in_array($status, $allowed, true)) {
      throw new \InvalidArgumentException('Invalid product status.');
    }
    $this->status = $status;
  }

  /**
   * Define o código já calculado externamente.
   * Regras:
   * - 6 dígitos numéricos
   * - prefixo (2 primeiros dígitos) deve bater com o tipo atual
   */
  public function setCode(string $code): void
  {
    if (!preg_match('/^\d{6}$/', $code)) {
      throw new \InvalidArgumentException('Invalid product code format. Expected 6 digits.');
    }

    $expectedPrefix = $this->type->getCodePrefix();
    if (substr($code, 0, 2) !== $expectedPrefix) {
      throw new \InvalidArgumentException('Product code prefix does not match product type.');
    }

    $this->code = $code;
  }

  /**
   * Helper para definir o código a partir de um sequence number (> 0)
   */
  public function setCodeFromSequence(int $sequence): void
  {
    $this->setCode(self::generateCode($this->type, $sequence));
  }

  /**
   * Gera o código a partir do tipo e do sequence number (> 0).
   * Formato: %02d%04d (ex.: 01 + 0005 => 010005)
   */
  public static function generateCode(ProductType $type, int $sequence): string
  {
    if ($sequence <= 0) {
      throw new \InvalidArgumentException('Sequence must be positive.');
    }
    if ($sequence > 9999) {
      throw new \InvalidArgumentException('Sequence must be <= 9999.');
    }

    $prefix = $type->getCodePrefix();
    $seq = str_pad((string)$sequence, 4, '0', STR_PAD_LEFT);

    return $prefix . $seq;
  }

  public function changeStatus(string $status): void
  {
    $this->setStatus($status);
  }
}