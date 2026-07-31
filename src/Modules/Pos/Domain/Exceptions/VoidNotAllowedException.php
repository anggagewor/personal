<?php

namespace Modules\Pos\Domain\Exceptions;

class VoidNotAllowedException extends \DomainException
{
    public static function alreadyVoided(string $transactionNumber): self
    {
        return new self("Transaksi '{$transactionNumber}' sudah dibatalkan sebelumnya.");
    }

    public static function notCompleted(string $transactionNumber): self
    {
        return new self("Transaksi '{$transactionNumber}' tidak dapat dibatalkan karena statusnya belum selesai.");
    }
}
