<?php

namespace Modules\Pos\Domain\Exceptions;

class InvalidVoucherException extends \DomainException
{
    public static function expired(string $code): self
    {
        return new self("Voucher '{$code}' sudah kedaluwarsa.");
    }

    public static function fullyRedeemed(string $code): self
    {
        return new self("Voucher '{$code}' sudah habis digunakan.");
    }

    public static function minimumPurchaseNotMet(string $code, float $minPurchase): self
    {
        $formatted = number_format($minPurchase, 0, ',', '.');

        return new self("Voucher '{$code}' membutuhkan minimum pembelian Rp {$formatted}.");
    }

    public static function notFound(string $code): self
    {
        return new self("Voucher '{$code}' tidak ditemukan.");
    }

    public static function inactive(string $code): self
    {
        return new self("Voucher '{$code}' tidak aktif.");
    }

    public static function productNotInCart(string $code): self
    {
        return new self("Voucher '{$code}' hanya berlaku untuk produk tertentu yang belum ada di keranjang.");
    }
}
