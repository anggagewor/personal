<?php

namespace Modules\Tag\Infrastructure\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Modules\Note\Infrastructure\Models\NoteModel;
use Modules\Shared\Infrastructure\Traits\BelongsToUser;
use Modules\Task\Infrastructure\Models\TaskModel;

class TagModel extends Model
{
    use BelongsToUser, HasFactory;

    protected $table = 'tags';

    protected $fillable = [
        'user_id',
        'name',
        'color',
    ];

    public function notes(): MorphToMany
    {
        return $this->morphedByMany(NoteModel::class, 'taggable', 'taggables', 'tag_id', 'taggable_id');
    }

    public function tasks(): MorphToMany
    {
        return $this->morphedByMany(TaskModel::class, 'taggable', 'taggables', 'tag_id', 'taggable_id');
    }
}
