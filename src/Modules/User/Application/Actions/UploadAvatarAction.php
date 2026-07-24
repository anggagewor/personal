<?php

namespace Modules\User\Application\Actions;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\User\Domain\Contracts\UserRepositoryInterface;

class UploadAvatarAction
{
    public function __construct(
        private UserRepositoryInterface $repository,
    ) {}

    public function execute(int $userId, UploadedFile $file): string
    {
        $user = $this->repository->findById($userId);

        // Delete old avatar if exists
        if ($user->hasAvatar()) {
            Storage::disk('public')->delete($user->avatar);
        }

        // Store new avatar
        $path = $file->store('avatars', 'public');

        $this->repository->updateAvatar($userId, $path);

        return $path;
    }
}
