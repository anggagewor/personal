<?php

namespace Modules\Vault\Application\Actions;

use Modules\Vault\Domain\Contracts\VaultRepositoryInterface;

class DeleteVaultEntryAction
{
    public function __construct(
        private VaultRepositoryInterface $repository,
    ) {}

    public function execute(int $id): void
    {
        $this->repository->delete($id);
    }
}
