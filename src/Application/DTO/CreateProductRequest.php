<?php

namespace Chiarelli\DddApp\Application\DTO;

final class CreateProductRequest
{
    public string $name;
    public float $price;
    public int $productTypeId;

    public function __construct(string $name, float $price, int $productTypeId)
    {
        $this->name = $name;
        $this->price = $price;
        $this->productTypeId = $productTypeId;
    }
}