<?php

namespace Modules\Pos\Domain\Exceptions;

class RefundNotAllowedException extends \DomainException
{
    public static function notCompleted(string $transactionNumber): self
    {
        return new self("Transaksi '{$transactionNumber}' tidak dapat di-refund karena belum berstatus selesai.");
    }

    public static function alreadyFullyRefunded(string $transactionNumber): self
    {
        return new self("Transaksi '{$transactionNumber}' sudah di-refund seluruhnya.");
    }

    public static function exceedsRefundableAmount(string $transactionNumber, float $requested, float $available): self
    {
        return new self(
            "Refund untuk '{$transactionNumber}' melebihi jumlah yang dapat di-refund. " .
            "Diminta: " . number_format($requested, 0, ',', '.') . ", " .
            "Tersisa: " . number_format($available, 0, ',', '.') . "."
        );
    }

    public static function itemQuantityExceedsAvailable(string $productName, int $requested, int $available): self
    {
        return new self(
            "Jumlah refund untuk '{$productName}' melebihi yang tersedia. " .
            "Diminta: {$requested}, Tersisa: {$available}."
        );
    }

    public static function emptyItems(): self
    {
        return new self("Refund harus memiliki minimal 1 item.");
    }
}
