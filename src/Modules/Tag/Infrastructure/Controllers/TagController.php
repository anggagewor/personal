<?php

namespace Modules\Tag\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Tag\Application\Actions\AttachTagAction;
use Modules\Tag\Application\Actions\CreateTagAction;
use Modules\Tag\Application\Actions\DeleteTagAction;
use Modules\Tag\Application\Actions\DetachTagAction;
use Modules\Tag\Application\Actions\UpdateTagAction;
use Modules\Tag\Application\DTO\TagData;
use Modules\Tag\Domain\Contracts\TagRepositoryInterface;
use Modules\Tag\Infrastructure\Requests\StoreTagRequest;
use Modules\Tag\Infrastructure\Requests\UpdateTagRequest;

class TagController extends Controller
{
    public function __construct(
        private TagRepositoryInterface $repository,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tags = $this->repository->findByUser($request->user()->id);

        return response()->json([
            'data' => $tags,
        ]);
    }

    public function store(StoreTagRequest $request, CreateTagAction $action): JsonResponse
    {
        $tag = $action->execute(
            userId: $request->user()->id,
            data: TagData::fromArray($request->validated()),
        );

        return response()->json([
            'data' => $tag,
            'message' => 'Tag berhasil dibuat.',
        ], 201);
    }

    public function update(UpdateTagRequest $request, int $id, UpdateTagAction $action): JsonResponse
    {
        $tag = $this->repository->findById($id);

        if (!$tag || $tag->userId !== $request->user()->id) {
            abort(403);
        }

        $tag = $action->execute(
            tagId: $id,
            data: TagData::fromArray(array_merge(
                ['name' => $tag->name, 'color' => $tag->color],
                $request->validated(),
            )),
        );

        return response()->json([
            'data' => $tag,
            'message' => 'Tag berhasil diperbarui.',
        ]);
    }

    public function destroy(Request $request, int $id, DeleteTagAction $action): JsonResponse
    {
        $tag = $this->repository->findById($id);

        if (!$tag || $tag->userId !== $request->user()->id) {
            abort(403);
        }

        $action->execute($id);

        return response()->json([
            'message' => 'Tag berhasil dihapus.',
        ]);
    }

    public function attach(Request $request, int $id, AttachTagAction $action): JsonResponse
    {
        $tag = $this->repository->findById($id);

        if (!$tag || $tag->userId !== $request->user()->id) {
            abort(403);
        }

        $request->validate([
            'taggable_type' => ['required', 'string', 'in:note,task'],
            'taggable_id' => ['required', 'integer'],
        ]);

        $morphMap = [
            'note' => \Modules\Note\Infrastructure\Models\NoteModel::class,
            'task' => \Modules\Task\Infrastructure\Models\TaskModel::class,
        ];

        $action->execute($id, $morphMap[$request->input('taggable_type')], $request->input('taggable_id'));

        return response()->json([
            'message' => 'Tag berhasil dipasang.',
        ]);
    }

    public function detach(Request $request, int $id, DetachTagAction $action): JsonResponse
    {
        $tag = $this->repository->findById($id);

        if (!$tag || $tag->userId !== $request->user()->id) {
            abort(403);
        }

        $request->validate([
            'taggable_type' => ['required', 'string', 'in:note,task'],
            'taggable_id' => ['required', 'integer'],
        ]);

        $morphMap = [
            'note' => \Modules\Note\Infrastructure\Models\NoteModel::class,
            'task' => \Modules\Task\Infrastructure\Models\TaskModel::class,
        ];

        $action->execute($id, $morphMap[$request->input('taggable_type')], $request->input('taggable_id'));

        return response()->json([
            'message' => 'Tag berhasil dilepas.',
        ]);
    }
}
