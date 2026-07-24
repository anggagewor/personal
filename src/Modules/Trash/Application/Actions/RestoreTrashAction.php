<?php

namespace Modules\Trash\Application\Actions;

use Modules\Trash\Domain\Contracts\TrashRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RestoreTrashAction
{
    public function __construct(
        private TrashRepositoryInterface $repository,
    ) {}

    public function execute(string $type, int $id, int $userId): void
    {
        $this->validateType($type);

        $restored = $this->repository->restore($type, $id, $userId);

        if (!$restored) {
            throw new NotFoundHttpException('Data tidak ditemukan.');
        }
    }

    private function validateType(string $type): void
    {
        if (!in_array($type, ['note', 'task'])) {
            throw new NotFoundHttpException('Tipe tidak valid.');
        }
    }
}
