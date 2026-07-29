<?php

namespace Modules\Vault\Application\Actions;

use Modules\Vault\Application\DTO\VaultEntryData;
use Modules\Vault\Domain\Contracts\VaultRepositoryInterface;
use Modules\Vault\Domain\Entities\VaultEntry;

class UpdateVaultEntryAction
{
    public function __construct(
        private VaultRepositoryInterface $repository,
    ) {}

    public function execute(int $entryId, int $userId, VaultEntryData $data): VaultEntry
    {
        $entry = new VaultEntry(
            id: $entryId,
            userId: $userId,
            name: $data->name,
            username: $data->username,
            encryptedPassword: $data->encryptedPassword,
            url: $data->url,
            notes: $data->notes,
            category: $data->category,
        );

        return $this->repository->save($entry);
    }
}
