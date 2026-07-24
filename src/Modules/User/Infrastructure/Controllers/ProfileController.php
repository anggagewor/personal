<?php

namespace Modules\User\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Modules\User\Application\Actions\UpdatePasswordAction;
use Modules\User\Application\Actions\UpdateProfileAction;
use Modules\User\Application\Actions\UploadAvatarAction;
use Modules\User\Infrastructure\Requests\UpdatePasswordRequest;
use Modules\User\Infrastructure\Requests\UpdateProfileRequest;
use Modules\User\Infrastructure\Requests\UploadAvatarRequest;

class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar ? asset('storage/' . $user->avatar) : null,
                'email_verified_at' => $user->email_verified_at,
                'created_at' => $user->created_at,
            ],
        ]);
    }

    public function update(UpdateProfileRequest $request, UpdateProfileAction $action): JsonResponse
    {
        $user = $action->execute($request->user()->id, $request->validated());

        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar ? asset('storage/' . $user->avatar) : null,
            ],
            'message' => 'Profil berhasil diperbarui.',
        ]);
    }

    public function updatePassword(UpdatePasswordRequest $request, UpdatePasswordAction $action): JsonResponse
    {
        $action->execute($request->user()->id, $request->validated()['password']);

        return response()->json([
            'message' => 'Password berhasil diubah.',
        ]);
    }

    public function uploadAvatar(UploadAvatarRequest $request, UploadAvatarAction $action): JsonResponse
    {
        $path = $action->execute($request->user()->id, $request->file('avatar'));

        return response()->json([
            'data' => [
                'avatar' => asset('storage/' . $path),
            ],
            'message' => 'Foto profil berhasil diperbarui.',
        ]);
    }

    public function removeAvatar(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
            $user->avatar = null;
            $user->save();
        }

        return response()->json([
            'message' => 'Foto profil berhasil dihapus.',
        ]);
    }
}
