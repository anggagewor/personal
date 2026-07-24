<?php

namespace Modules\Tag\Infrastructure\Repositories;

use Illuminate\Support\Facades\DB;
use Modules\Tag\Domain\Contracts\TagRepositoryInterface;
use Modules\Tag\Domain\Entities\Tag;
use Modules\Tag\Infrastructure\Models\TagModel;

class EloquentTagRepository implements TagRepositoryInterface
{
    public function findById(int $id): ?Tag
    {
        $model = TagModel::find($id);

        if (!$model) {
            return null;
        }

        return $this->toEntity($model);
    }

    public function findByUser(int $userId): array
    {
        $models = TagModel::where('user_id', $userId)
            ->orderBy('name')
            ->get();

        return $models->map(fn ($model) => $this->toEntity($model))->toArray();
    }

    public function save(Tag $tag): Tag
    {
        $model = TagModel::updateOrCreate(
            ['id' => $tag->id],
            [
                'user_id' => $tag->userId,
                'name' => $tag->name,
                'color' => $tag->color,
            ]
        );

        return $this->toEntity($model->fresh());
    }

    public function delete(int $id): void
    {
        TagModel::where('id', $id)->delete();
    }

    public function attach(int $tagId, string $taggableType, int $taggableId): void
    {
        DB::table('taggables')->insertOrIgnore([
            'tag_id' => $tagId,
            'taggable_type' => $taggableType,
            'taggable_id' => $taggableId,
        ]);
    }

    public function detach(int $tagId, string $taggableType, int $taggableId): void
    {
        DB::table('taggables')
            ->where('tag_id', $tagId)
            ->where('taggable_type', $taggableType)
            ->where('taggable_id', $taggableId)
            ->delete();
    }

    private function toEntity(TagModel $model): Tag
    {
        return new Tag(
            id: $model->id,
            userId: $model->user_id,
            name: $model->name,
            color: $model->color,
        );
    }
}
