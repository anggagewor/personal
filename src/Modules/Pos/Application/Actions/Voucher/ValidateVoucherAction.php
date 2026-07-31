<?php

namespace Modules\Pos\Application\Actions\Voucher;

use DateTimeImmutable;
use Modules\Pos\Domain\Contracts\VoucherRepositoryInterface;
use Modules\Pos\Domain\Entities\Voucher;
use Modules\Pos\Domain\Exceptions\InvalidVoucherException;

class ValidateVoucherAction
{
    public function __construct(
        private VoucherRepositoryInterface $voucherRepo,
    ) {}

    public function execute(string $code, float $subtotal): Voucher
    {
        $voucher = $this->voucherRepo->findByCode($code);

        if ($voucher === null) {
            throw InvalidVoucherException::notFound($code);
        }

        if (! $voucher->isActive) {
            throw InvalidVoucherException::inactive($code);
        }

        if ($voucher->expiresAt !== null && $voucher->expiresAt < new DateTimeImmutable()) {
            throw InvalidVoucherException::expired($code);
        }

        if ($voucher->usageLimit !== null && $voucher->usageCount >= $voucher->usageLimit) {
            throw InvalidVoucherException::fullyRedeemed($code);
        }

        if ($voucher->minPurchase !== null && $subtotal < $voucher->minPurchase) {
            throw InvalidVoucherException::minimumPurchaseNotMet($code, $voucher->minPurchase);
        }

        return $voucher;
    }
}
