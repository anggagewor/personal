<?php

namespace Modules\Tag\Application\Actions;

use Modules\Tag\Application\DTO\TagData;
use Modules\Tag\Domain\Contracts\TagRepositoryInterface;
use Modules\Tag\Domain\Entities\Tag;

class UpdateTagAction
{
    public function __construct(
        private TagRepositoryInterface $repository,
    ) {}

    public function execute(int $tagId, TagData $data): Tag
    {
        $tag = $this->repository->findById($tagId);

        $tag->name = $data->name;
        $tag->color = $data->color;

        return $this->repository->save($tag);
    }
}
