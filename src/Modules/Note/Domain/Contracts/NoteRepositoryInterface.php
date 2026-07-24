<?php

namespace Modules\Note\Domain\Contracts;

use Modules\Note\Domain\Entities\Note;

interface NoteRepositoryInterface
{
    public function findById(int $id): ?Note;

    public function findByUserPaginated(int $userId, ?string $search = null, int $perPage = 15): array;

    public function save(Note $note): Note;

    public function delete(int $id): void;
}
