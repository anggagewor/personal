<?php

namespace Modules\AuditLog\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\AuditLog\Application\Actions\QueryAuditLogAction;
use Modules\AuditLog\Application\DTO\AuditLogQuery;
use Modules\AuditLog\Domain\Enums\AuditEvent;
use Modules\AuditLog\Infrastructure\Requests\QueryAuditLogRequest;
use Modules\AuditLog\Infrastructure\Resources\AuditLogResource;

class AuditLogController extends Controller
{
    public function __construct(
        private QueryAuditLogAction $queryAction,
    ) {}

    public function index(QueryAuditLogRequest $request): JsonResponse
    {
        $query = new AuditLogQuery(
            userId: $request->user()->id,
            event: $request->validated('event') ? AuditEvent::from($request->validated('event')) : null,
            auditableType: $request->validated('auditable_type'),
            auditableId: $request->validated('auditable_id') ? (int) $request->validated('auditable_id') : null,
            tags: $request->validated('tags'),
            dateFrom: $request->validated('date_from'),
            dateTo: $request->validated('date_to'),
            perPage: (int) ($request->validated('per_page') ?? 15),
        );

        $result = $this->queryAction->execute($query);

        return response()->json([
            'data' => AuditLogResource::collection($result['data']),
            'meta' => $result['meta'],
        ]);
    }

    public function auditable(Request $request, string $type, int $id): JsonResponse
    {
        $query = new AuditLogQuery(
            userId: $request->user()->id,
            auditableType: $type,
            auditableId: $id,
            perPage: (int) $request->query('per_page', 15),
        );

        $result = $this->queryAction->execute($query);

        return response()->json([
            'data' => AuditLogResource::collection($result['data']),
            'meta' => $result['meta'],
        ]);
    }
}
