<?php

namespace Modules\Vault\Application\DTO;

readonly class VaultEntryData
{
    public function __construct(
        public string $name,
        public ?string $username = null,
        public string $encryptedPassword = '',
        public ?string $url = null,
        public ?string $notes = null,
        public ?string $category = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            username: $data['username'] ?? null,
            encryptedPassword: $data['encrypted_password'] ?? '',
            url: $data['url'] ?? null,
            notes: $data['notes'] ?? null,
            category: $data['category'] ?? null,
        );
    }
}
