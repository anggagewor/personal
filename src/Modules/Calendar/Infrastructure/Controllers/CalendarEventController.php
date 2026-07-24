<?php

namespace Modules\Calendar\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Calendar\Application\Actions\CreateCalendarEventAction;
use Modules\Calendar\Application\Actions\DeleteCalendarEventAction;
use Modules\Calendar\Application\Actions\UpdateCalendarEventAction;
use Modules\Calendar\Application\DTO\CalendarEventData;
use Modules\Calendar\Domain\Contracts\CalendarEventRepositoryInterface;
use Modules\Calendar\Infrastructure\Requests\StoreCalendarEventRequest;
use Modules\Calendar\Infrastructure\Requests\UpdateCalendarEventRequest;

class CalendarEventController extends Controller
{
    public function __construct(
        private CalendarEventRepositoryInterface $repository,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->endOfMonth()->toDateString());

        $events = $this->repository->findByUserAndDateRange(
            userId: $request->user()->id,
            startDate: $startDate,
            endDate: $endDate,
        );

        return response()->json([
            'data' => $events,
        ]);
    }

    public function store(StoreCalendarEventRequest $request, CreateCalendarEventAction $action): JsonResponse
    {
        $event = $action->execute(
            userId: $request->user()->id,
            data: CalendarEventData::fromArray($request->validated()),
        );

        return response()->json([
            'data' => $event,
            'message' => 'Event berhasil dibuat.',
        ], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $event = $this->repository->findById($id);

        if (!$event || $event->userId !== $request->user()->id) {
            abort(403);
        }

        return response()->json([
            'data' => $event,
        ]);
    }

    public function update(UpdateCalendarEventRequest $request, int $id, UpdateCalendarEventAction $action): JsonResponse
    {
        $event = $this->repository->findById($id);

        if (!$event || $event->userId !== $request->user()->id) {
            abort(403);
        }

        $event = $action->execute(
            eventId: $id,
            data: CalendarEventData::fromArray(array_merge(
                [
                    'title' => $event->title,
                    'description' => $event->description,
                    'start_at' => $event->startDate?->format('Y-m-d H:i:s'),
                    'end_at' => $event->endDate?->format('Y-m-d H:i:s'),
                    'color' => $event->color,
                    'all_day' => $event->isAllDay,
                ],
                $request->validated(),
            )),
        );

        return response()->json([
            'data' => $event,
            'message' => 'Event berhasil diperbarui.',
        ]);
    }

    public function destroy(Request $request, int $id, DeleteCalendarEventAction $action): JsonResponse
    {
        $event = $this->repository->findById($id);

        if (!$event || $event->userId !== $request->user()->id) {
            abort(403);
        }

        $action->execute($id);

        return response()->json([
            'message' => 'Event berhasil dihapus.',
        ]);
    }
}
