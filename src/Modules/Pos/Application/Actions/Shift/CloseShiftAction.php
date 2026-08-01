<?php

namespace Modules\Pos\Application\Actions\Shift;

use Modules\Pos\Application\DTO\CloseShiftData;
use Modules\Pos\Domain\Contracts\ShiftRepositoryInterface;
use Modules\Pos\Domain\Entities\CashierShift;
use Modules\Pos\Domain\Exceptions\ShiftException;

class CloseShiftAction
{
    public function __construct(
        private ShiftRepositoryInterface $shiftRepo,
    ) {}

    public function execute(CloseShiftData $data): CashierShift
    {
        $shift = $this->shiftRepo->findById($data->shiftId);

        if ($shift === null) {
            throw ShiftException::notFound($data->shiftId);
        }

        if ($shift->isClosed()) {
            throw ShiftException::alreadyClosed($data->shiftId);
        }

        // Calculate expected amount: opening + cash_sales - cash_refunds
        $cashSales = $this->shiftRepo->getCashSalesDuringShift($data->shiftId);
        $cashRefunds = $this->shiftRepo->getCashRefundsDuringShift($data->shiftId);
        $expectedAmount = $shift->openingAmount + $cashSales - $cashRefunds;

        return $this->shiftRepo->close(
            id: $data->shiftId,
            closingAmount: $data->closingAmount,
            expectedAmount: $expectedAmount,
            notes: $data->notes,
        );
    }
}
