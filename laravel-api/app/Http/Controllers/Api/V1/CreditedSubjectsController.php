<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\CreditSubjectStoreRequest;
use App\Services\CreditedSubjectsService;
use Illuminate\Http\Request;

class CreditedSubjectsController extends Controller
{
    protected CreditedSubjectsService $service;

    public function __construct(CreditedSubjectsService $service)
    {
        $this->service = $service;
    }

    /**
     * GET /api/v1/students/{student_number}/credits
     * List credited subjects for a student (registrar/admin).
     */
    public function index(string $student_number)
    {
        try {
            $rows = $this->service->list($student_number);
            return response()->json([
                'success' => true,
                'data'    => $rows,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * POST /api/v1/students/{student_number}/credits
     * Create a credited subject entry for a student (registrar/admin).
     */
    public function store(CreditSubjectStoreRequest $request, string $student_number)
    {
        $subjectId   = (int) $request->input('subject_id');
        $termTaken   = $request->input('term_taken');
        $schoolTaken = $request->input('school_taken');
        $remarks     = $request->input('remarks');

        try {
            $row = $this->service->create($student_number, $subjectId, $termTaken, $schoolTaken, $remarks, $request);
            return response()->json([
                'success' => true,
                'data'    => $row,
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * DELETE /api/v1/students/{student_number}/credits/{id}
     * Delete a credited subject entry (registrar/admin).
     */
    public function destroy(Request $request, string $student_number, int $id)
    {
        try {
            $ok = $this->service->delete($student_number, (int) $id, $request);
            return response()->json([
                'success' => (bool) $ok,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
