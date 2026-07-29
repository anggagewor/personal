<?php

namespace Modules\GoogleDrive\Application\Actions;

use Modules\GoogleDrive\Domain\Contracts\DriveConnectionRepositoryInterface;
use Modules\GoogleDrive\Domain\Contracts\DriveServiceInterface;
use Modules\Note\Infrastructure\Models\NoteModel;

class SyncNotesToDriveAction
{
    public function __construct(
        private DriveConnectionRepositoryInterface $repository,
        private DriveServiceInterface $driveService,
    ) {}

    /**
     * Sync all user notes to Google Drive as individual HTML files.
     * @return int Number of notes synced
     */
    public function execute(int $userId): int
    {
        $connection = $this->repository->findByUserId($userId);

        if (!$connection) {
            throw new \RuntimeException('Google Drive belum terhubung.');
        }

        $accessToken = $this->ensureValidToken($connection);

        // Ensure notes folder exists
        $folderId = $this->ensureNotesFolder($accessToken);

        $notes = NoteModel::where('user_id', $userId)->get();
        $count = 0;

        foreach ($notes as $note) {
            $filename = $this->sanitizeFilename($note->title) . '.html';
            $content = $this->wrapHtml($note->title, $note->content ?? '');

            $this->driveService->uploadFile(
                $accessToken,
                $filename,
                $content,
                'text/html',
                $folderId,
            );

            $count++;
        }

        return $count;
    }

    private function ensureNotesFolder(string $accessToken): string
    {
        $files = $this->driveService->listFiles($accessToken, null, "name = 'Purdia Notes' and mimeType = 'application/vnd.google-apps.folder'");

        if (!empty($files)) {
            return $files[0]->id;
        }

        $folder = $this->driveService->createFolder($accessToken, 'Purdia Notes');

        return $folder->id;
    }

    private function sanitizeFilename(string $name): string
    {
        return preg_replace('/[^\w\s\-.]/', '', $name) ?: 'untitled';
    }

    private function wrapHtml(string $title, string $body): string
    {
        return "<!DOCTYPE html><html><head><meta charset=\"utf-8\"><title>{$title}</title></head><body><h1>{$title}</h1>{$body}</body></html>";
    }

    private function ensureValidToken(mixed $connection): string
    {
        if ($connection->isExpired()) {
            $refreshed = $this->driveService->refreshAccessToken($connection->refreshToken);

            $connection = new \Modules\GoogleDrive\Domain\Entities\DriveConnection(
                id: $connection->id,
                userId: $connection->userId,
                accessToken: $refreshed['access_token'],
                refreshToken: $connection->refreshToken,
                email: $connection->email,
                tokenExpiresAt: new \DateTimeImmutable("+{$refreshed['expires_in']} seconds"),
            );

            $this->repository->save($connection);

            return $refreshed['access_token'];
        }

        return $connection->accessToken;
    }
}
