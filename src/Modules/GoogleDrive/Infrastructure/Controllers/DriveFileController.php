<?php

namespace Modules\GoogleDrive\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\GoogleDrive\Application\Actions\BackupToDriveAction;
use Modules\GoogleDrive\Application\Actions\SyncNotesToDriveAction;
use Modules\GoogleDrive\Domain\Contracts\DriveConnectionRepositoryInterface;
use Modules\GoogleDrive\Domain\Contracts\DriveServiceInterface;

class DriveFileController extends Controller
{
    public function __construct(
        private DriveServiceInterface $driveService,
        private DriveConnectionRepositoryInterface $repository,
    ) {}

    /**
     * List files in Drive (optionally in a folder).
     */
    public function index(Request $request): JsonResponse
    {
        $accessToken = $this->getAccessToken($request->user()->id);
        $folderId = $request->query('folder_id');

        $files = $this->driveService->listFiles($accessToken, $folderId);

        return response()->json([
            'data' => $files,
        ]);
    }

    /**
     * Upload a file to Drive.
     */
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'max:10240'], // 10MB max
            'folder_id' => ['nullable', 'string'],
        ]);

        $accessToken = $this->getAccessToken($request->user()->id);
        $uploadedFile = $request->file('file');

        $driveFile = $this->driveService->uploadFile(
            $accessToken,
            $uploadedFile->getClientOriginalName(),
            file_get_contents($uploadedFile->getRealPath()),
            $uploadedFile->getMimeType(),
            $request->input('folder_id'),
        );

        return response()->json([
            'data' => $driveFile,
            'message' => 'File berhasil diupload ke Google Drive.',
        ], 201);
    }

    /**
     * Download a file from Drive.
     */
    public function download(Request $request, string $fileId): \Illuminate\Http\Response
    {
        $accessToken = $this->getAccessToken($request->user()->id);
        $content = $this->driveService->downloadFile($accessToken, $fileId);

        return response($content, 200)
            ->header('Content-Type', 'application/octet-stream')
            ->header('Content-Disposition', 'attachment');
    }

    /**
     * Delete a file from Drive.
     */
    public function destroy(Request $request, string $fileId): JsonResponse
    {
        $accessToken = $this->getAccessToken($request->user()->id);
        $this->driveService->deleteFile($accessToken, $fileId);

        return response()->json([
            'message' => 'File berhasil dihapus dari Google Drive.',
        ]);
    }

    /**
     * Create a folder in Drive.
     */
    public function createFolder(Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'string'],
        ]);

        $accessToken = $this->getAccessToken($request->user()->id);

        $folder = $this->driveService->createFolder(
            $accessToken,
            $request->input('name'),
            $request->input('parent_id'),
        );

        return response()->json([
            'data' => $folder,
            'message' => 'Folder berhasil dibuat.',
        ], 201);
    }

    /**
     * Backup all data to Drive.
     */
    public function backup(Request $request, BackupToDriveAction $action): JsonResponse
    {
        $file = $action->execute($request->user()->id);

        return response()->json([
            'data' => $file,
            'message' => 'Backup berhasil diupload ke Google Drive.',
        ]);
    }

    /**
     * Sync notes to Drive.
     */
    public function syncNotes(Request $request, SyncNotesToDriveAction $action): JsonResponse
    {
        $count = $action->execute($request->user()->id);

        return response()->json([
            'data' => ['synced_count' => $count],
            'message' => "{$count} catatan berhasil disinkronkan ke Google Drive.",
        ]);
    }

    /**
     * Get valid access token, refreshing if needed.
     */
    private function getAccessToken(int $userId): string
    {
        $connection = $this->repository->findByUserId($userId);

        if (!$connection) {
            abort(400, 'Google Drive belum terhubung. Hubungkan dulu di Pengaturan.');
        }

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
