<?php

namespace Modules\GoogleDrive\Application\Actions;

use Modules\GoogleDrive\Domain\Contracts\DriveConnectionRepositoryInterface;
use Modules\GoogleDrive\Domain\Contracts\DriveServiceInterface;
use Modules\GoogleDrive\Domain\Entities\DriveFile;
use Modules\Note\Infrastructure\Models\NoteModel;
use Modules\Task\Infrastructure\Models\TaskModel;
use Modules\Bookmark\Infrastructure\Models\BookmarkModel;
use Modules\Finance\Infrastructure\Models\FinanceModel;
use Modules\Habit\Infrastructure\Models\HabitModel;
use Modules\Journal\Infrastructure\Models\JournalModel;

class BackupToDriveAction
{
    public function __construct(
        private DriveConnectionRepositoryInterface $repository,
        private DriveServiceInterface $driveService,
    ) {}

    public function execute(int $userId): DriveFile
    {
        $connection = $this->repository->findByUserId($userId);

        if (!$connection) {
            throw new \RuntimeException('Google Drive belum terhubung.');
        }

        $accessToken = $this->ensureValidToken($connection);

        // Collect all user data
        $data = [
            'exported_at' => now()->toIso8601String(),
            'notes' => NoteModel::where('user_id', $userId)->get()->toArray(),
            'tasks' => TaskModel::where('user_id', $userId)->get()->toArray(),
            'bookmarks' => BookmarkModel::where('user_id', $userId)->get()->toArray(),
            'finances' => FinanceModel::where('user_id', $userId)->get()->toArray(),
            'habits' => HabitModel::where('user_id', $userId)->get()->toArray(),
            'journals' => JournalModel::where('user_id', $userId)->get()->toArray(),
        ];

        $content = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $filename = 'purdia-backup-' . date('Y-m-d-His') . '.json';

        // Ensure backup folder exists
        $folderId = $this->ensureBackupFolder($accessToken);

        return $this->driveService->uploadFile(
            $accessToken,
            $filename,
            $content,
            'application/json',
            $folderId,
        );
    }

    private function ensureBackupFolder(string $accessToken): string
    {
        $files = $this->driveService->listFiles($accessToken, null, "name = 'Purdia Backups' and mimeType = 'application/vnd.google-apps.folder'");

        if (!empty($files)) {
            return $files[0]->id;
        }

        $folder = $this->driveService->createFolder($accessToken, 'Purdia Backups');

        return $folder->id;
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
