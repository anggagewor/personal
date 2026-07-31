<?php

namespace Modules\Supplier\Application\Actions\Supplier;

use Modules\Supplier\Application\DTO\SupplierData;
use Modules\Supplier\Domain\Contracts\SupplierRepositoryInterface;
use Modules\Supplier\Domain\Entities\Supplier;
use Modules\Supplier\Domain\Exceptions\DuplicateSupplierException;

class UpdateSupplierAction
{
    public function __construct(
        private SupplierRepositoryInterface $repository,
    ) {}

    public function execute(int $id, SupplierData $data): Supplier
    {
        $supplier = $this->repository->findById($id);

        if ($this->repository->existsByName($supplier->outletId, $data->name, $id)) {
            throw DuplicateSupplierException::create($data->name);
        }

        return $this->repository->update($id, $data);
    }
}
