<?php

namespace Chiarelli\DddApp\Domain\Entity;

final class ProductType
{
  private ?int $id;
  private string $name;
  private string $normalizedName;

  public function __construct(string $name, ?int $id = null)
  {
    $this->setName($name);
    if ($id !== null && $id <= 0) {
      throw new \InvalidArgumentException('Product type id must be positive when provided.');
    }
    $this->id = $id;
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  public function getName(): string
  {
    return $this->name;
  }

  public function getNormalizedName(): string
  {
    return $this->normalizedName;
  }

  public function setName(string $name): void
  {
    $trimmed = trim($name);
    if ($trimmed === '') {
      throw new \InvalidArgumentException('Product type name cannot be empty.');
    }
    $this->name = $trimmed;
    $this->normalizedName = strtolower($trimmed);
  }

  /**
   * Retorna prefixo usado para gerar o código do produto.
   * Exemplo: tipo ID = 1 → prefixo "01"
   */
  public function getCodePrefix(): string
  {
    if (!$this->id) {
      throw new \LogicException('Cannot generate code prefix without ProductType ID.');
    }
    return str_pad((string)$this->id, 2, '0', STR_PAD_LEFT);
  }
}