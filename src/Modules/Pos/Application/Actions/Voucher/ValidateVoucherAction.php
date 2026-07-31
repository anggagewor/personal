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

    /**
     * Validate a voucher code against the current cart.
     *
     * @param string $code
     * @param float $subtotal Total cart subtotal
     * @param array $items Cart items: [['product_id' => int, 'subtotal' => float], ...]
     * @return Voucher
     * @throws InvalidVoucherException
     */
    public function execute(string $code, float $subtotal, array $items = []): Voucher
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

        // Product-specific voucher: check if the target product is in the cart
        if ($voucher->productId !== null && !empty($items)) {
            $productInCart = false;
            foreach ($items as $item) {
                if (($item['product_id'] ?? 0) === $voucher->productId) {
                    $productInCart = true;
                    break;
                }
            }
            if (!$productInCart) {
                throw InvalidVoucherException::productNotInCart($code);
            }
        }

        return $voucher;
    }
}
