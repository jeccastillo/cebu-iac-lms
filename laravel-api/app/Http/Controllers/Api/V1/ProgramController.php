<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\ProgramStoreRequest;
use App\Http\Requests\ProgramUpdateRequest;
use App\Services\SystemLogService;

class ProgramController extends Controller
{
    /**
     * GET /api/v1/programs
     * Query params:
     *  - enabledOnly: bool (default true) => when true, filter enumEnabled != 0
     */
    public function index(Request $request)
    {
        $enabledOnly = filter_var($request->query('enabledOnly', 'true'), FILTER_VALIDATE_BOOLEAN);

        $query = Program::query();

        if ($enabledOnly) {
            // In CI, Data_fetcher filters programs with enumEnabled != 0
            $query->where('enumEnabled', '!=', 0);
        }

        // Optional filters: type, school, search (code/description/major)
        if ($request->filled('type')) {
            $query->where('type', $request->query('type'));
        }
        if ($request->filled('school')) {
            $query->where('school', $request->query('school'));
        }
        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $like = '%' . str_replace(['%', '_'], ['\\%','\\_'], $search) . '%';
            $query->where(function ($q) use ($like) {
                $q->where('strProgramCode', 'like', $like)
                  ->orWhere('strProgramDescription', 'like', $like)
                  ->orWhere('strMajor', 'like', $like);
            });
        }

        $programs = $query->orderBy('strProgramDescription', 'asc')->get();

        // Map to the structure used by PortalApi::view_active_programs
        $data = $programs->map(function ($prog) {
            return [
                'id' => $prog->intProgramID,
                'title' => $prog->strProgramDescription,
                'type' => $prog->type,
                'strMajor' => $prog->strMajor,
                'strProgramCode' => $prog->strProgramCode,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * GET /api/v1/programs/{id}
     */
    public function show(int $id): JsonResponse
    {
        $program = Program::find($id);
        if (!$program) {
            return response()->json([
                'success' => false,
                'message' => 'Program not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $program,
        ]);
    }

    /**
     * POST /api/v1/programs
     * Body: payload validating ProgramStoreRequest
     */
    public function store(ProgramStoreRequest $request): JsonResponse
    {
        $data = $request->validated();
        if (!array_key_exists('enumEnabled', $data)) {
            $data['enumEnabled'] = 1;
        }

        $program = Program::create($data);

        // System log: create
        SystemLogService::log('create', 'Program', $program->getKey(), null, $program->toArray(), $request);

        return response()->json([
            'success' => true,
            'data' => $program,
        ], 201);
    }

    /**
     * PUT /api/v1/programs/{id}
     * Body: payload validating ProgramUpdateRequest
     */
    public function update(ProgramUpdateRequest $request, int $id): JsonResponse
    {
        $program = Program::find($id);
        if (!$program) {
            return response()->json([
                'success' => false,
                'message' => 'Program not found',
            ], 404);
        }

        $old = $program->toArray();
        $data = $request->validated();

        if (empty($data)) {
            return response()->json([
                'success' => true,
                'data' => $program,
            ]);
        }

        $program->update($data);

        $new = $program->fresh();

        // System log: update
        SystemLogService::log('update', 'Program', $program->getKey(), $old, $new->toArray(), $request);

        return response()->json([
            'success' => true,
            'data' => $new,
        ]);
    }

    /**
     * DELETE /api/v1/programs/{id}
     * Soft disable by setting enumEnabled=0
     */
    public function destroy(int $id): JsonResponse
    {
        $program = Program::find($id);
        if (!$program) {
            return response()->json([
                'success' => false,
                'message' => 'Program not found',
            ], 404);
        }

        $old = $program->toArray();
        $program->update(['enumEnabled' => 0]);
        $new = $program->fresh()->toArray();

        // System log: update (soft-disable)
        SystemLogService::log('update', 'Program', $program->getKey(), $old, $new, request());

        return response()->json([
            'success' => true,
            'message' => 'Program disabled',
        ]);
    }
}
