<?php

namespace Modules\Supplier\Application\Actions\Supplier;

use Modules\Supplier\Application\DTO\SupplierData;
use Modules\Supplier\Domain\Contracts\SupplierRepositoryInterface;
use Modules\Supplier\Domain\Entities\Supplier;
use Modules\Supplier\Domain\Exceptions\DuplicateSupplierException;

class CreateSupplierAction
{
    public function __construct(
        private SupplierRepositoryInterface $repository,
    ) {}

    public function execute(int $outletId, SupplierData $data): Supplier
    {
        if ($this->repository->existsByName($outletId, $data->name)) {
            throw DuplicateSupplierException::create($data->name);
        }

        return $this->repository->create($outletId, $data);
    }
}
