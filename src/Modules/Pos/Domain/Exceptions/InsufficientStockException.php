<?php

namespace Modules\Pos\Domain\Exceptions;

class InsufficientStockException extends \RuntimeException
{
    public static function create(string $productName, int $available, int $requested): self
    {
        return new self("Stok tidak mencukupi untuk produk '{$productName}'. Tersedia: {$available}, diminta: {$requested}.");
    }
}
