<?php

namespace Modules\Finance\Application\Actions;

use DateTimeImmutable;
use Modules\Finance\Application\DTO\FinanceData;
use Modules\Finance\Domain\Contracts\FinanceRepositoryInterface;
use Modules\Finance\Domain\Entities\Finance;
use Modules\Finance\Domain\Enums\FinanceType;

class CreateFinanceAction
{
    public function __construct(
        private FinanceRepositoryInterface $repository,
    ) {}

    public function execute(int $userId, FinanceData $data): Finance
    {
        $finance = new Finance(
            id: null,
            userId: $userId,
            type: FinanceType::from($data->type),
            amount: $data->amount,
            category: $data->category,
            description: $data->description,
            date: $data->date ? new DateTimeImmutable($data->date) : new DateTimeImmutable(),
        );

        return $this->repository->save($finance);
    }
}
