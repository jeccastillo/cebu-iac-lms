<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Clinic\HealthRecordStoreRequest;
use App\Http\Requests\Api\V1\Clinic\HealthRecordUpdateRequest;
use App\Http\Resources\Clinic\HealthRecordListResource;
use App\Http\Resources\Clinic\HealthRecordResource;
use App\Models\ClinicHealthRecord;
use App\Services\ClinicHealthService;
use App\Services\SystemLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClinicHealthController extends Controller
{
    protected ClinicHealthService $service;

    public function __construct(ClinicHealthService $service)
    {
        $this->service = $service;
    }

    /**
     * GET /api/v1/clinic/records
     * Filters: q, student_number, faculty_id, last_name, first_name, middle_name, campus_id, program_id, year_level,
     *          diagnosis, medication, allergy, date_from, date_to, page, per_page
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only([
            'q', 'student_number', 'faculty_id', 'last_name', 'first_name', 'middle_name',
            'campus_id', 'program_id', 'year_level',
            'diagnosis', 'medication', 'allergy',
            'date_from', 'date_to',
        ]);

        $page = (int) $request->query('page', 1);
        $perPage = (int) $request->query('per_page', 20);

        $paginator = $this->service->search($filters, $page, $perPage);

        $data = [];
        foreach ($paginator->items() as $item) {
            $data[] = (new HealthRecordListResource($item))->toArray($request);
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
     * POST /api/v1/clinic/records
     * Create or update a record for a person (upsert).
     */
    public function store(HealthRecordStoreRequest $request): JsonResponse
    {
        $payload = $request->validated();
        // stamp last_updated_by via request resolver if present
        if (!isset($payload['last_updated_by'])) {
            $payload['last_updated_by'] = $request->input('last_updated_by');
        }

        $record = $this->service->createOrUpdate($payload);

        SystemLogService::log('create', 'ClinicHealthRecord', $record->id, null, $record, $request);

        return response()->json([
            'success' => true,
            'data' => new HealthRecordResource($record->loadCount('visits')),
        ]);
    }

    /**
     * GET /api/v1/clinic/records/{id}
     */
    public function show(int $id, Request $request): JsonResponse
    {
        $record = $this->service->get($id);

        if (!$record) {
            return response()->json([
                'success' => false,
                'message' => 'Health record not found.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new HealthRecordResource($record),
        ]);
    }

    /**
     * PUT /api/v1/clinic/records/{id}
     * Update basic properties of an existing record (does not change person linkage).
     */
    public function update(int $id, HealthRecordUpdateRequest $request): JsonResponse
    {
        /** @var ClinicHealthRecord|null $record */
        $record = ClinicHealthRecord::find($id);
        if (!$record) {
            return response()->json([
                'success' => false,
                'message' => 'Health record not found.'
            ], 404);
        }

        $validated = $request->validated();
        $old = clone $record;

        $record->fill($validated);
        // stamp last_updated_by
        if (isset($validated['last_updated_by'])) {
            $record->last_updated_by = (int) $validated['last_updated_by'];
        }
        $record->save();

        SystemLogService::log('update', 'ClinicHealthRecord', $record->id, $old, $record, $request);

        return response()->json([
            'success' => true,
            'data' => new HealthRecordResource($record->loadCount('visits')),
        ]);
    }
}
