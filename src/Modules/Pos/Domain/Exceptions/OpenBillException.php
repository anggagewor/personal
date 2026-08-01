<?php

namespace Modules\Pos\Domain\Exceptions;

class OpenBillException extends \DomainException
{
    public static function notFound(int $id): self
    {
        return new self("Open bill dengan ID {$id} tidak ditemukan.");
    }

    public static function notPending(string $transactionNumber): self
    {
        return new self("Transaksi '{$transactionNumber}' tidak berstatus pending, tidak dapat ditutup.");
    }

    public static function alreadyClosed(string $transactionNumber): self
    {
        return new self("Transaksi '{$transactionNumber}' sudah ditutup sebelumnya.");
    }
}
