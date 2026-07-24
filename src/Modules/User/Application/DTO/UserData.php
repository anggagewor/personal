<?php

namespace Modules\User\Application\DTO;

readonly class UserData
{
    public function __construct(
        public string $name,
        public string $email,
        public ?string $password = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            email: $data['email'],
            password: $data['password'] ?? null,
        );
    }
}
