<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\SchoolYearStoreRequest;
use App\Http\Requests\Api\V1\SchoolYearUpdateRequest;
use App\Models\SchoolYear;
use App\Services\SystemLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class SchoolYearController extends Controller
{
    /**
     * GET /api/v1/school-years
     * Query params (optional):
     *  - campus_id: int
     *  - term_student_type: string
     *  - search: string (matches enumSem, strYearStart, strYearEnd)
     *  - limit: int (optional)
     */
    public function index(Request $request): JsonResponse
    {
        $q = SchoolYear::query();

        if ($request->filled('campus_id')) {
            $q->where('campus_id', (int) $request->query('campus_id'));
        }
        if ($request->filled('term_student_type')) {
            $q->where('term_student_type', $request->query('term_student_type'));
        }
        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $like = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $search) . '%';
            $q->where(function ($sub) use ($like) {
                $sub->where('enumSem', 'like', $like)
                    ->orWhere('strYearStart', 'like', $like)
                    ->orWhere('strYearEnd', 'like', $like);
            });
        }

        $q->orderBy('strYearStart', 'desc')->orderBy('enumSem', 'asc');

        if ($request->filled('limit')) {
            $limit = (int) $request->query('limit', 50);
            $data = $q->limit($limit)->get();
        } else {
            $data = $q->get();
        }

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }

    /**
     * GET /api/v1/school-years/{id}
     */
    public function show(int $id): JsonResponse
    {
    $item = SchoolYear::find($id);
        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'School Year not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $item,
        ]);
    }

    /**
     * POST /api/v1/school-years
     */
    public function store(SchoolYearStoreRequest $request): JsonResponse
    {
        $data = $request->validated();

        if (!array_key_exists('term_label', $data) || $data['term_label'] === null || $data['term_label'] === '') {
            $data['term_label'] = 'Semester';
        }

        // Ensure invalid '0000-00-00 00:00:00' isn't saved
        foreach (['midterm_start','midterm_end','final_start','final_end','end_of_submission'] as $dt) {
            if (isset($data[$dt]) && $data[$dt] === '0000-00-00 00:00:00') {
                $data[$dt] = null;
            }
        }

        try {
            $item = SchoolYear::create($data);
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle composite unique constraint violation gracefully
            $msg = $e->getMessage();
            $isDuplicate = $e->getCode() === '23000'
                || stripos($msg, 'Duplicate entry') !== false
                || stripos($msg, 'ux_sy_year_sem_campus') !== false;
            if ($isDuplicate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Duplicate term for this campus. Combination of strYearStart, strYearEnd, and enumSem must be unique per campus_id.',
                    'errors'  => [
                        'unique' => ['The combination (strYearStart, strYearEnd, enumSem, campus_id) must be unique.']
                    ],
                ], 422);
            }
            throw $e;
        }

        // System log: create
        SystemLogService::log('create', 'SchoolYear', $item->getKey(), null, $item->toArray(), $request);

        return response()->json([
            'success' => true,
            'data'    => $item,
        ], 201);
    }

    /**
     * PUT /api/v1/school-years/{id}
     */
    public function update(SchoolYearUpdateRequest $request, int $id): JsonResponse
    {
        $item = SchoolYear::find($id);
        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'School Year not found',
            ], 404);
        }

        $old = $item->toArray();
        $data = $request->validated();

        if (empty($data)) {
            return response()->json([
                'success' => true,
                'data'    => $item,
            ]);
        }

        // Normalize invalid datetime
        foreach (['midterm_start','midterm_end','final_start','final_end','end_of_submission'] as $dt) {
            if (array_key_exists($dt, $data) && $data[$dt] === '0000-00-00 00:00:00') {
                $data[$dt] = null;
            }
        }

        try {
            $item->update($data);
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle composite unique constraint violation gracefully
            $msg = $e->getMessage();
            $isDuplicate = $e->getCode() === '23000'
                || stripos($msg, 'Duplicate entry') !== false
                || stripos($msg, 'ux_sy_year_sem_campus') !== false;
            if ($isDuplicate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Duplicate term for this campus. Combination of strYearStart, strYearEnd, and enumSem must be unique per campus_id.',
                    'errors'  => [
                        'unique' => ['The combination (strYearStart, strYearEnd, enumSem, campus_id) must be unique.']
                    ],
                ], 422);
            }
            throw $e;
        }
        $new = $item->fresh();

        // System log: update
        SystemLogService::log('update', 'SchoolYear', $item->getKey(), $old, $new->toArray(), $request);

        return response()->json([
            'success' => true,
            'data'    => $new,
        ]);
    }

    /**
     * DELETE /api/v1/school-years/{id}
     * Soft-disable via enumStatus if column exists; otherwise hard delete.
     */
    public function destroy(int $id): JsonResponse
    {
        $item = SchoolYear::find($id);
        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'School Year not found',
            ], 404);
        }

        $old = $item->toArray();

        $softDisabled = false;
        try {
            if (Schema::hasColumn('tb_mas_sy', 'enumStatus')) {
                $item->update(['enumStatus' => 'inactive']);
                $softDisabled = true;
            } else {
                $item->delete();
            }
        } catch (\Throwable $e) {
            // Fallback to hard delete if update fails for any reason
            try {
                $item->delete();
            } catch (\Throwable $e2) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete School Year',
                ], 500);
            }
        }

        $new = $softDisabled ? $item->fresh()->toArray() : null;

        // System log
        if ($softDisabled) {
            SystemLogService::log('update', 'SchoolYear', $item->getKey(), $old, $new, request());
        } else {
            SystemLogService::log('delete', 'SchoolYear', $id, $old, null, request());
        }

        return response()->json([
            'success' => true,
            'message' => $softDisabled ? 'School Year disabled' : 'School Year deleted',
        ]);
    }
}
