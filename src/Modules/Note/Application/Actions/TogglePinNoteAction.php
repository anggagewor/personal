<?php

namespace Modules\Note\Application\Actions;

use Modules\Note\Domain\Contracts\NoteRepositoryInterface;
use Modules\Note\Domain\Entities\Note;

class TogglePinNoteAction
{
    public function __construct(
        private NoteRepositoryInterface $repository,
    ) {}

    public function execute(int $noteId): Note
    {
        $note = $this->repository->findById($noteId);
        $note->togglePin();

        return $this->repository->save($note);
    }
}
