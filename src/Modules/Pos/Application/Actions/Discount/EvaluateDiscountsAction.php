<?php

namespace Modules\Pos\Application\Actions\Discount;

use Modules\Pos\Domain\Contracts\DiscountRepositoryInterface;
use Modules\Pos\Domain\Entities\Discount;

class EvaluateDiscountsAction
{
    public function __construct(
        private DiscountRepositoryInterface $discountRepo,
    ) {}

    /**
     * Evaluate and return applicable discounts for a given outlet and cart items.
     *
     * @param int $outletId
     * @param float $subtotal Total cart subtotal
     * @param int|null $memberId
     * @param array $items Cart items: [['product_id' => int, 'quantity' => int, 'subtotal' => float], ...]
     * @return array{applicable: Discount[], total_discount: float}
     */
    public function execute(int $outletId, float $subtotal, ?int $memberId = null, array $items = []): array
    {
        $discounts = $this->discountRepo->findApplicable($outletId, $subtotal, $memberId);

        if (empty($discounts)) {
            return ['applicable' => [], 'total_discount' => 0.0];
        }

        // Collect product IDs in cart for product-specific discount filtering
        $cartProductIds = array_column($items, 'product_id');

        try {
            // Sort by priority ascending (lower = higher priority, applied first)
            usort($discounts, function (Discount $a, Discount $b): int {
                return $a->priority <=> $b->priority;
            });

            $applicable = [];
            $totalDiscount = 0.0;
            $remaining = $subtotal;

            foreach ($discounts as $discount) {
                // Check conditions for ALL discounts (hours, days, min_qty, etc.)
                if (!$this->meetsConditions($discount, $items)) {
                    continue;
                }

                // Product-specific discount: skip if product not in cart
                if ($discount->productId !== null) {
                    if (!in_array($discount->productId, $cartProductIds, true)) {
                        continue;
                    }
                }

                // Re-check min purchase against remaining subtotal
                if ($discount->minPurchase !== null && $remaining < $discount->minPurchase) {
                    continue;
                }

                // Calculate discount amount
                $baseAmount = $this->getDiscountBase($discount, $items, $remaining);
                $discountAmount = $this->calculateDiscountAmount($discount, $baseAmount);

                if ($discountAmount <= 0) {
                    continue;
                }

                $applicable[] = $discount;
                $totalDiscount += $discountAmount;
                $remaining -= $discountAmount;
            }

            return ['applicable' => $applicable, 'total_discount' => $totalDiscount];
        } catch (\Throwable) {
            // Graceful degradation
            return ['applicable' => $discounts, 'total_discount' => 0.0];
        }
    }

    /**
     * Determine the base amount for discount calculation.
     * Product-specific discounts apply to the product's subtotal only.
     * General discounts apply to the full remaining subtotal.
     */
    private function getDiscountBase(Discount $discount, array $items, float $remaining): float
    {
        if ($discount->productId !== null && !empty($items)) {
            // Sum subtotal of matching product items only
            $productSubtotal = 0.0;
            foreach ($items as $item) {
                if (($item['product_id'] ?? 0) === $discount->productId) {
                    $productSubtotal += (float) ($item['subtotal'] ?? 0);
                }
            }
            return $productSubtotal;
        }

        return $remaining;
    }

    private function calculateDiscountAmount(Discount $discount, float $base): float
    {
        return match ($discount->type->value) {
            'percentage' => $base * ($discount->value / 100),
            'fixed' => min($discount->value, $base),
            default => 0.0,
        };
    }

    /**
     * Check if discount conditions are met (e.g. min_qty, hours, days).
     */
    private function meetsConditions(Discount $discount, array $items): bool
    {
        if (empty($discount->conditions)) {
            return true;
        }

        // Check min_qty condition
        if (isset($discount->conditions['min_qty']) && $discount->productId !== null) {
            $minQty = (int) $discount->conditions['min_qty'];
            $totalQty = 0;

            foreach ($items as $item) {
                if (($item['product_id'] ?? 0) === $discount->productId) {
                    $totalQty += (int) ($item['quantity'] ?? 0);
                }
            }

            if ($totalQty < $minQty) {
                return false;
            }
        }

        // Check hours condition (e.g. {"hours": {"start": "14:00", "end": "17:00"}})
        if (isset($discount->conditions['hours'])) {
            $now = now();
            $currentTime = $now->format('H:i');
            $start = $discount->conditions['hours']['start'] ?? '00:00';
            $end = $discount->conditions['hours']['end'] ?? '23:59';

            if ($currentTime < $start || $currentTime > $end) {
                return false;
            }
        }

        // Check days condition (e.g. {"days": ["Saturday", "Sunday"]})
        if (isset($discount->conditions['days'])) {
            $today = now()->format('l'); // e.g. "Friday"
            if (!in_array($today, $discount->conditions['days'], true)) {
                return false;
            }
        }

        // Check segment condition (e.g. {"segment": "student"})
        // Segment-based discounts require member validation at a higher level
        // For now, skip if segment is set (treat as member_only equivalent)
        if (isset($discount->conditions['segment'])) {
            // Segment discounts are handled separately via member linking
            // If no member is provided, these should already be filtered by findApplicable
            return true;
        }

        return true;
    }
}
