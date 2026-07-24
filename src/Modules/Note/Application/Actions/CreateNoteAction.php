<?php

namespace Modules\Note\Application\Actions;

use Modules\Note\Application\DTO\NoteData;
use Modules\Note\Domain\Contracts\NoteRepositoryInterface;
use Modules\Note\Domain\Entities\Note;

class CreateNoteAction
{
    public function __construct(
        private NoteRepositoryInterface $repository,
    ) {}

    public function execute(int $userId, NoteData $data): Note
    {
        $note = new Note(
            id: null,
            userId: $userId,
            title: $data->title,
            content: $data->content,
            isPinned: $data->isPinned,
        );

        return $this->repository->save($note);
    }
}
