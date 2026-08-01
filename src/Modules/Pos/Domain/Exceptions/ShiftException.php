<?php

namespace Modules\Pos\Domain\Exceptions;

class ShiftException extends \DomainException
{
    public static function alreadyOpen(string $cashierName): self
    {
        return new self("Kasir '{$cashierName}' sudah memiliki shift yang aktif.");
    }

    public static function notFound(int $id): self
    {
        return new self("Shift dengan ID {$id} tidak ditemukan.");
    }

    public static function alreadyClosed(int $id): self
    {
        return new self("Shift #{$id} sudah ditutup sebelumnya.");
    }

    public static function noActiveShift(): self
    {
        return new self("Tidak ada shift aktif. Silakan buka shift terlebih dahulu.");
    }
}
