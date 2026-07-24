<?php

namespace Modules\Tag\Application\Actions;

use Modules\Tag\Application\DTO\TagData;
use Modules\Tag\Domain\Contracts\TagRepositoryInterface;
use Modules\Tag\Domain\Entities\Tag;

class CreateTagAction
{
    public function __construct(
        private TagRepositoryInterface $repository,
    ) {}

    public function execute(int $userId, TagData $data): Tag
    {
        $tag = new Tag(
            id: null,
            userId: $userId,
            name: $data->name,
            color: $data->color,
        );

        return $this->repository->save($tag);
    }
}
