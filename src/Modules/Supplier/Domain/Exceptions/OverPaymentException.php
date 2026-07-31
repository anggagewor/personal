<?php

namespace Modules\Supplier\Domain\Exceptions;

class OverPaymentException extends \DomainException
{
    public static function create(string $poNumber, float $outstanding, float $attempted): self
    {
        $outstandingFormatted = number_format($outstanding, 0, ',', '.');
        $attemptedFormatted = number_format($attempted, 0, ',', '.');

        return new self("Pembayaran untuk PO '{$poNumber}' melebihi sisa tagihan. Sisa: Rp {$outstandingFormatted}, dicoba: Rp {$attemptedFormatted}.");
    }
}
