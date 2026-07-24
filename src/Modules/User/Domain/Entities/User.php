<?php

namespace Modules\User\Domain\Entities;

use DateTimeImmutable;

class User
{
    public function __construct(
        public ?int $id,
        public string $name,
        public string $email,
        public ?string $password = null,
        public ?string $avatar = null,
        public array $preferences = [],
        public ?DateTimeImmutable $emailVerifiedAt = null,
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $updatedAt = null,
    ) {}

    public function getPreference(string $key, mixed $default = null): mixed
    {
        return $this->preferences[$key] ?? $default;
    }

    public function setPreference(string $key, mixed $value): void
    {
        $this->preferences[$key] = $value;
    }

    public function getPreferencesWithDefaults(): array
    {
        $defaults = [
            'theme' => 'system',
            'primary_color' => 'indigo',
            'locale' => 'id',
            'sidebar_collapsed' => false,
        ];

        return array_merge($defaults, $this->preferences);
    }

    public function hasAvatar(): bool
    {
        return $this->avatar !== null;
    }
}
