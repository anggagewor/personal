<?php

namespace Modules\Vault\Domain\Contracts;

use Modules\Vault\Domain\Entities\VaultEntry;

interface VaultRepositoryInterface
{
    public function findById(int $id): ?VaultEntry;

    public function findByUser(int $userId, ?string $search = null, ?string $category = null): array;

    public function save(VaultEntry $entry): VaultEntry;

    public function delete(int $id): void;

    public function getCategories(int $userId): array;
}
