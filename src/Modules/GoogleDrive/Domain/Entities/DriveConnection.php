<?php

namespace Modules\GoogleDrive\Domain\Entities;

use DateTimeImmutable;

class DriveConnection
{
    public function __construct(
        public ?int $id,
        public int $userId,
        public string $accessToken,
        public string $refreshToken,
        public ?string $email = null,
        public ?DateTimeImmutable $tokenExpiresAt = null,
        public ?DateTimeImmutable $createdAt = null,
    ) {}

    public function isExpired(): bool
    {
        if (!$this->tokenExpiresAt) {
            return true;
        }

        return $this->tokenExpiresAt < new DateTimeImmutable();
    }
}
