<?php

namespace Modules\Finance\Application\Actions;

use DateTimeImmutable;
use Modules\Finance\Application\DTO\FinanceData;
use Modules\Finance\Domain\Contracts\FinanceRepositoryInterface;
use Modules\Finance\Domain\Entities\Finance;
use Modules\Finance\Domain\Enums\FinanceType;

class UpdateFinanceAction
{
    public function __construct(
        private FinanceRepositoryInterface $repository,
    ) {}

    public function execute(int $financeId, FinanceData $data): Finance
    {
        $finance = $this->repository->findById($financeId);

        $finance->type = FinanceType::from($data->type);
        $finance->amount = $data->amount;
        $finance->category = $data->category;
        $finance->description = $data->description;
        $finance->date = $data->date ? new DateTimeImmutable($data->date) : $finance->date;

        return $this->repository->save($finance);
    }
}
