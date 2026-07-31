<?php

namespace Modules\Pos\Application\Actions\Voucher;

use Modules\Pos\Application\DTO\VoucherData;
use Modules\Pos\Domain\Contracts\VoucherRepositoryInterface;
use Modules\Pos\Domain\Entities\Voucher;

class BatchCreateVoucherAction
{
    public function __construct(
        private VoucherRepositoryInterface $voucherRepo,
    ) {}

    /**
     * Generate batch vouchers with unique codes using the given prefix.
     *
     * @return Voucher[]
     */
    public function execute(int $outletId, string $prefix, int $count, VoucherData $templateData): array
    {
        $vouchers = [];

        $generatedCodes = [];
        for ($i = 0; $i < $count; $i++) {
            $code = $this->generateUniqueCode($prefix, $generatedCodes);
            $generatedCodes[] = $code;

            $vouchers[] = new VoucherData(
                code: $code,
                type: $templateData->type,
                value: $templateData->value,
                minPurchase: $templateData->minPurchase,
                usageLimit: $templateData->usageLimit,
                expiresAt: $templateData->expiresAt,
                isActive: $templateData->isActive,
            );
        }

        return $this->voucherRepo->batchCreate($outletId, $vouchers);
    }

    private function generateUniqueCode(string $prefix, array $existingCodes): string
    {
        do {
            $suffix = strtoupper(bin2hex(random_bytes(4)));
            $code = "{$prefix}-{$suffix}";
        } while (in_array($code, $existingCodes, true));

        return $code;
    }
}
