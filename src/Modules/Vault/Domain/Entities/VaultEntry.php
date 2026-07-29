<?php

namespace Modules\Vault\Domain\Entities;

use DateTimeImmutable;

class VaultEntry
{
    public function __construct(
        public ?int $id,
        public int $userId,
        public string $name,
        public ?string $username = null,
        public string $encryptedPassword = '', // ciphertext from client
        public ?string $url = null,
        public ?string $notes = null,
        public ?string $category = null,
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $updatedAt = null,
    ) {}
}
