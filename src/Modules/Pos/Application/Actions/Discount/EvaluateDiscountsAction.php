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
     * Evaluate and return applicable discounts for a given outlet, subtotal, and optional member.
     *
     * Flow:
     * 1. Get applicable discounts from repository (already filtered by active, date range, min purchase, member_only)
     * 2. Try to sort by priority (ascending — lower priority number = applied first)
     * 3. Apply discounts in priority order, recalculating remaining after each application
     * 4. On any sorting/priority error: apply individually qualifying discounts without ordering (graceful degradation)
     *
     * @return Discount[]
     */
    public function execute(int $outletId, float $subtotal, ?int $memberId = null): array
    {
        $discounts = $this->discountRepo->findApplicable($outletId, $subtotal, $memberId);

        if (empty($discounts)) {
            return [];
        }

        try {
            // Sort by priority ascending (lower = higher priority, applied first)
            usort($discounts, function (Discount $a, Discount $b): int {
                return $a->priority <=> $b->priority;
            });

            // Apply discounts in priority order, recalculating remaining subtotal
            $applicable = [];
            $remaining = $subtotal;

            foreach ($discounts as $discount) {
                // Re-check min purchase against remaining subtotal after prior applications
                if ($discount->minPurchase !== null && $remaining < $discount->minPurchase) {
                    continue;
                }

                $applicable[] = $discount;

                // Recalculate remaining subtotal after applying this discount
                $remaining -= $this->calculateDiscountAmount($discount, $remaining);
            }

            return $applicable;
        } catch (\Throwable) {
            // Graceful degradation: on any priority/sorting error,
            // return individually qualifying discounts without ordering (Req 7.10)
            return $discounts;
        }
    }

    private function calculateDiscountAmount(Discount $discount, float $subtotal): float
    {
        return match ($discount->type->value) {
            'percentage' => $subtotal * ($discount->value / 100),
            'fixed' => min($discount->value, $subtotal),
            default => 0.0,
        };
    }
}
