<?php

namespace Modules\GoogleDrive\Domain\Entities;

class DriveFile
{
    public function __construct(
        public string $id,
        public string $name,
        public string $mimeType,
        public ?int $size = null,
        public ?string $iconLink = null,
        public ?string $webViewLink = null,
        public ?string $createdTime = null,
        public ?string $modifiedTime = null,
        public ?string $parentId = null,
    ) {}

    public function isFolder(): bool
    {
        return $this->mimeType === 'application/vnd.google-apps.folder';
    }
}
