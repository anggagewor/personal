<?php

namespace Modules\GoogleDrive\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\GoogleDrive\Application\Actions\ConnectDriveAction;
use Modules\GoogleDrive\Application\Actions\DisconnectDriveAction;
use Modules\GoogleDrive\Application\DTO\DriveConnectionData;
use Modules\GoogleDrive\Domain\Contracts\DriveConnectionRepositoryInterface;
use Modules\GoogleDrive\Domain\Contracts\DriveServiceInterface;

class DriveAuthController extends Controller
{
    public function __construct(
        private DriveServiceInterface $driveService,
        private DriveConnectionRepositoryInterface $repository,
    ) {}

    /**
     * Get connection status.
     */
    public function status(Request $request): JsonResponse
    {
        $connection = $this->repository->findByUserId($request->user()->id);

        if (!$connection) {
            return response()->json([
                'data' => ['connected' => false],
            ]);
        }

        return response()->json([
            'data' => [
                'connected' => true,
                'email' => $connection->email,
                'connected_at' => $connection->createdAt?->format('Y-m-d H:i:s'),
            ],
        ]);
    }

    /**
     * Get OAuth authorization URL.
     */
    public function authUrl(Request $request): JsonResponse
    {
        $redirectUri = config('services.google.redirect_uri');
        $url = $this->driveService->getAuthUrl($redirectUri);

        return response()->json([
            'data' => ['url' => $url],
        ]);
    }

    /**
     * Handle OAuth callback — exchange code for tokens and store.
     */
    public function callback(Request $request, ConnectDriveAction $action): JsonResponse
    {
        $request->validate([
            'code' => ['required', 'string'],
        ]);

        $redirectUri = config('services.google.redirect_uri');
        $tokens = $this->driveService->exchangeCode($request->input('code'), $redirectUri);

        $connection = $action->execute(
            userId: $request->user()->id,
            data: new DriveConnectionData(
                accessToken: $tokens['access_token'],
                refreshToken: $tokens['refresh_token'],
                email: $tokens['email'],
                expiresIn: $tokens['expires_in'],
            ),
        );

        return response()->json([
            'data' => [
                'connected' => true,
                'email' => $connection->email,
            ],
            'message' => 'Google Drive berhasil terhubung.',
        ]);
    }

    /**
     * Disconnect Google Drive.
     */
    public function disconnect(Request $request, DisconnectDriveAction $action): JsonResponse
    {
        $action->execute($request->user()->id);

        return response()->json([
            'message' => 'Google Drive berhasil diputus.',
        ]);
    }
}
