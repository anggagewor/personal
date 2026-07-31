<?php

namespace Modules\Pos\Domain\Exceptions;

class InvalidStockAdjustmentException extends \DomainException
{
    public static function zeroQuantity(): self
    {
        return new self('Jumlah penyesuaian stok tidak boleh nol.');
    }
}
