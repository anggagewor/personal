<?php

namespace Modules\Note\Application\Actions;

use Modules\Note\Application\DTO\NoteData;
use Modules\Note\Domain\Contracts\NoteRepositoryInterface;
use Modules\Note\Domain\Entities\Note;

class UpdateNoteAction
{
    public function __construct(
        private NoteRepositoryInterface $repository,
    ) {}

    public function execute(int $noteId, NoteData $data): Note
    {
        $note = $this->repository->findById($noteId);

        $note->title = $data->title;
        $note->content = $data->content;
        $note->isPinned = $data->isPinned;

        return $this->repository->save($note);
    }
}
