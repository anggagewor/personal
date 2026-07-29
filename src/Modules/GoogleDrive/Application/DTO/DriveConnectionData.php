<?php

namespace Modules\GoogleDrive\Application\DTO;

readonly class DriveConnectionData
{
    public function __construct(
        public string $accessToken,
        public string $refreshToken,
        public ?string $email = null,
        public ?int $expiresIn = null,
    ) {}
}
