<?php

namespace Modules\Note\Application\Actions;

use Modules\Note\Domain\Contracts\NoteRepositoryInterface;

class DeleteNoteAction
{
    public function __construct(
        private NoteRepositoryInterface $repository,
    ) {}

    public function execute(int $noteId): void
    {
        $this->repository->delete($noteId);
    }
}
