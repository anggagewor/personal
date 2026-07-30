<?php

namespace Modules\Converter\Application\Actions;

use Modules\Converter\Application\DTO\CustomUnitData;
use Modules\Converter\Domain\Contracts\CustomUnitRepositoryInterface;
use Modules\Converter\Domain\Entities\CustomUnit;

class CreateCustomUnitAction
{
    public function __construct(
        private CustomUnitRepositoryInterface $repository,
    ) {}

    public function execute(CustomUnitData $data): CustomUnit
    {
        $unit = new CustomUnit(
            id: null,
            categoryId: $data->categoryId,
            name: $data->name,
            symbol: $data->symbol,
            toBase: $data->toBase,
            isBase: $data->isBase,
        );

        return $this->repository->save($unit);
    }
}
