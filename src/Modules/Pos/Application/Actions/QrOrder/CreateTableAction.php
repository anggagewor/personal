<?php

namespace Modules\Pos\Application\Actions\QrOrder;

use Modules\Pos\Domain\Contracts\OutletRepositoryInterface;
use Modules\Pos\Domain\Contracts\TableRepositoryInterface;
use Modules\Pos\Domain\Entities\Table;

class CreateTableAction
{
    public function __construct(
        private TableRepositoryInterface $tableRepository,
        private OutletRepositoryInterface $outletRepository,
    ) {}

    /**
     * Create a table for an outlet.
     * Only kafe/warkop business types support table ordering.
     *
     * @throws \RuntimeException If outlet not found or business type doesn't support table ordering.
     */
    public function execute(int $outletId, string $name): Table
    {
        $outlet = $this->outletRepository->findById($outletId);

        if ($outlet === null) {
            throw new \RuntimeException('Outlet tidak ditemukan.');
        }

        if (! $outlet->supportsTableOrdering()) {
            throw new \RuntimeException('Tipe bisnis outlet ini tidak mendukung pemesanan meja.');
        }

        return $this->tableRepository->create($outletId, $name);
    }
}
