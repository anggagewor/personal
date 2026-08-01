<?php

namespace Modules\Pos\Application\Actions\Shift;

use Modules\Pos\Application\DTO\OpenShiftData;
use Modules\Pos\Domain\Contracts\ShiftRepositoryInterface;
use Modules\Pos\Domain\Entities\CashierShift;
use Modules\Pos\Domain\Exceptions\ShiftException;

class OpenShiftAction
{
    public function __construct(
        private ShiftRepositoryInterface $shiftRepo,
    ) {}

    public function execute(OpenShiftData $data): CashierShift
    {
        // Check if user already has an active shift for this outlet
        $existingShift = $this->shiftRepo->findActiveByOutletAndUser($data->outletId, $data->userId);

        if ($existingShift !== null) {
            throw ShiftException::alreadyOpen($data->cashierName);
        }

        return $this->shiftRepo->create(
            outletId: $data->outletId,
            userId: $data->userId,
            cashierName: $data->cashierName,
            openingAmount: $data->openingAmount,
        );
    }
}
