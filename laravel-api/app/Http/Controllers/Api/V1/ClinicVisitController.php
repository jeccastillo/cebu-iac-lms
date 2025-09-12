<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Clinic\VisitStoreRequest;
use App\Http\Requests\Api\V1\Clinic\VisitUpdateRequest;
use App\Http\Resources\Clinic\VisitResource;
use App\Models\ClinicVisit;
use App\Services\ClinicVisitService;
use App\Services\SystemLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClinicVisitController extends Controller
{
    protected ClinicVisitService $service;

    public function __construct(ClinicVisitService $service)
    {
        $this->service = $service;
    }

    /**
     * GET /api/v1/clinic/visits?record_id=...&date_from=&date_to=&page=&per_page=
     */
    public function index(Request $request): JsonResponse
    {
        $recordId = (int) $request->query('record_id', 0);
        if ($recordId <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'record_id is required'
            ], 422);
        }

        $filters = $request->only(['date_from', 'date_to']);
        $page = (int) $request->query('page', 1);
        $perPage = (int) $request->query('per_page', 20);

        $paginator = $this->service->listByRecord($recordId, $filters, $page, $perPage);

        $data = [];
        foreach ($paginator->items() as $item) {
            $data[] = (new VisitResource($item))->toArray($request);
        }

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'total' => $paginator->total(),
                'page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }

    /**
     * POST /api/v1/clinic/visits
     */
    public function store(VisitStoreRequest $request): JsonResponse
    {
        $payload = $request->validated();

        $visit = $this->service->create($payload);

        SystemLogService::log('create', 'ClinicVisit', $visit->id, null, $visit, $request);

        return response()->json([
            'success' => true,
            'data' => new VisitResource($visit),
        ]);
    }

    /**
     * GET /api/v1/clinic/visits/{id}
     */
    public function show(int $id, Request $request): JsonResponse
    {
        $visit = ClinicVisit::find($id);
        if (!$visit) {
            return response()->json([
                'success' => false,
                'message' => 'Visit not found.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new VisitResource($visit),
        ]);
    }

    /**
     * PUT /api/v1/clinic/visits/{id}
     */
    public function update(int $id, VisitUpdateRequest $request): JsonResponse
    {
        $current = ClinicVisit::find($id);
        if (!$current) {
            return response()->json([
                'success' => false,
                'message' => 'Visit not found.'
            ], 404);
        }

        $old = clone $current;

        $visit = $this->service->update($id, $request->validated());

        SystemLogService::log('update', 'ClinicVisit', $visit->id, $old, $visit, $request);

        return response()->json([
            'success' => true,
            'data' => new VisitResource($visit),
        ]);
    }
}
