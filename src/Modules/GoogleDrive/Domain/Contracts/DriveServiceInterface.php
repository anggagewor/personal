<?php

namespace Modules\GoogleDrive\Domain\Contracts;

use Modules\GoogleDrive\Domain\Entities\DriveFile;

interface DriveServiceInterface
{
    /**
     * Get the OAuth authorization URL.
     */
    public function getAuthUrl(string $redirectUri): string;

    /**
     * Exchange authorization code for tokens.
     * @return array{access_token: string, refresh_token: string, expires_in: int, email: string}
     */
    public function exchangeCode(string $code, string $redirectUri): array;

    /**
     * Refresh an expired access token.
     * @return array{access_token: string, expires_in: int}
     */
    public function refreshAccessToken(string $refreshToken): array;

    /**
     * List files in a folder (default: root).
     * @return DriveFile[]
     */
    public function listFiles(string $accessToken, ?string $folderId = null, ?string $query = null): array;

    /**
     * Upload a file to Google Drive.
     */
    public function uploadFile(string $accessToken, string $name, string $content, string $mimeType, ?string $folderId = null): DriveFile;

    /**
     * Download file content.
     */
    public function downloadFile(string $accessToken, string $fileId): string;

    /**
     * Delete a file.
     */
    public function deleteFile(string $accessToken, string $fileId): void;

    /**
     * Create a folder.
     */
    public function createFolder(string $accessToken, string $name, ?string $parentId = null): DriveFile;
}
