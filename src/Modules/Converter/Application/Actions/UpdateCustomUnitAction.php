<?php

namespace Modules\Converter\Application\Actions;

use Modules\Converter\Application\DTO\CustomUnitData;
use Modules\Converter\Domain\Contracts\CustomUnitRepositoryInterface;
use Modules\Converter\Domain\Entities\CustomUnit;

class UpdateCustomUnitAction
{
    public function __construct(
        private CustomUnitRepositoryInterface $repository,
    ) {}

    public function execute(int $unitId, CustomUnitData $data): CustomUnit
    {
        $unit = new CustomUnit(
            id: $unitId,
            categoryId: $data->categoryId,
            name: $data->name,
            symbol: $data->symbol,
            toBase: $data->toBase,
            isBase: $data->isBase,
        );

        return $this->repository->save($unit);
    }
}
