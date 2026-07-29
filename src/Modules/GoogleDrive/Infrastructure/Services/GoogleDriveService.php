<?php

namespace Modules\GoogleDrive\Infrastructure\Services;

use Google\Client as GoogleClient;
use Google\Service\Drive as GoogleDrive;
use Google\Service\Drive\DriveFile as GoogleDriveFile;
use Google\Service\Oauth2 as GoogleOauth2;
use Modules\GoogleDrive\Domain\Contracts\DriveServiceInterface;
use Modules\GoogleDrive\Domain\Entities\DriveFile;

class GoogleDriveService implements DriveServiceInterface
{
    private function getClient(): GoogleClient
    {
        $client = new GoogleClient();
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setAccessType('offline');
        $client->setPrompt('consent');
        $client->addScope(GoogleDrive::DRIVE_FILE);
        $client->addScope('email');

        return $client;
    }

    public function getAuthUrl(string $redirectUri): string
    {
        $client = $this->getClient();
        $client->setRedirectUri($redirectUri);

        return $client->createAuthUrl();
    }

    public function exchangeCode(string $code, string $redirectUri): array
    {
        $client = $this->getClient();
        $client->setRedirectUri($redirectUri);

        $token = $client->fetchAccessTokenWithAuthCode($code);

        if (isset($token['error'])) {
            throw new \RuntimeException('Google OAuth error: ' . ($token['error_description'] ?? $token['error']));
        }

        $client->setAccessToken($token);

        // Get user email
        $oauth2 = new GoogleOauth2($client);
        $userInfo = $oauth2->userinfo->get();

        return [
            'access_token' => $token['access_token'],
            'refresh_token' => $token['refresh_token'] ?? '',
            'expires_in' => $token['expires_in'] ?? 3600,
            'email' => $userInfo->getEmail(),
        ];
    }

    public function refreshAccessToken(string $refreshToken): array
    {
        $client = $this->getClient();
        $client->fetchAccessTokenWithRefreshToken($refreshToken);
        $token = $client->getAccessToken();

        return [
            'access_token' => $token['access_token'],
            'expires_in' => $token['expires_in'] ?? 3600,
        ];
    }

    public function listFiles(string $accessToken, ?string $folderId = null, ?string $query = null): array
    {
        $service = $this->getDriveService($accessToken);

        $params = [
            'pageSize' => 50,
            'fields' => 'files(id,name,mimeType,size,iconLink,webViewLink,createdTime,modifiedTime,parents)',
            'orderBy' => 'folder,name',
        ];

        if ($query) {
            $params['q'] = $query;
        } elseif ($folderId) {
            $params['q'] = "'{$folderId}' in parents and trashed = false";
        } else {
            $params['q'] = "'root' in parents and trashed = false";
        }

        $results = $service->files->listFiles($params);
        $files = [];

        foreach ($results->getFiles() as $file) {
            $files[] = new DriveFile(
                id: $file->getId(),
                name: $file->getName(),
                mimeType: $file->getMimeType(),
                size: $file->getSize() ? (int) $file->getSize() : null,
                iconLink: $file->getIconLink(),
                webViewLink: $file->getWebViewLink(),
                createdTime: $file->getCreatedTime(),
                modifiedTime: $file->getModifiedTime(),
                parentId: $file->getParents()[0] ?? null,
            );
        }

        return $files;
    }

    public function uploadFile(string $accessToken, string $name, string $content, string $mimeType, ?string $folderId = null): DriveFile
    {
        $service = $this->getDriveService($accessToken);

        $fileMetadata = new GoogleDriveFile([
            'name' => $name,
        ]);

        if ($folderId) {
            $fileMetadata->setParents([$folderId]);
        }

        $file = $service->files->create($fileMetadata, [
            'data' => $content,
            'mimeType' => $mimeType,
            'uploadType' => 'multipart',
            'fields' => 'id,name,mimeType,size,webViewLink,createdTime',
        ]);

        return new DriveFile(
            id: $file->getId(),
            name: $file->getName(),
            mimeType: $file->getMimeType(),
            size: $file->getSize() ? (int) $file->getSize() : null,
            webViewLink: $file->getWebViewLink(),
            createdTime: $file->getCreatedTime(),
        );
    }

    public function downloadFile(string $accessToken, string $fileId): string
    {
        $service = $this->getDriveService($accessToken);

        $response = $service->files->get($fileId, ['alt' => 'media']);

        return (string) $response->getBody();
    }

    public function deleteFile(string $accessToken, string $fileId): void
    {
        $service = $this->getDriveService($accessToken);
        $service->files->delete($fileId);
    }

    public function createFolder(string $accessToken, string $name, ?string $parentId = null): DriveFile
    {
        $service = $this->getDriveService($accessToken);

        $metadata = new GoogleDriveFile([
            'name' => $name,
            'mimeType' => 'application/vnd.google-apps.folder',
        ]);

        if ($parentId) {
            $metadata->setParents([$parentId]);
        }

        $folder = $service->files->create($metadata, [
            'fields' => 'id,name,mimeType,webViewLink,createdTime',
        ]);

        return new DriveFile(
            id: $folder->getId(),
            name: $folder->getName(),
            mimeType: $folder->getMimeType(),
            webViewLink: $folder->getWebViewLink(),
            createdTime: $folder->getCreatedTime(),
        );
    }

    private function getDriveService(string $accessToken): GoogleDrive
    {
        $client = $this->getClient();
        $client->setAccessToken(['access_token' => $accessToken]);

        return new GoogleDrive($client);
    }
}
