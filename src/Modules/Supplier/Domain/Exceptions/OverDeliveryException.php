<?php

namespace Modules\Supplier\Domain\Exceptions;

class OverDeliveryException extends \DomainException
{
    public static function create(string $productName, int $maxReceivable, int $attempted): self
    {
        return new self("Jumlah penerimaan untuk produk '{$productName}' melebihi batas. Maksimal: {$maxReceivable}, dicoba: {$attempted}.");
    }
}
