<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\RegistrationService;
use App\Services\ClasslistService;

class RegistrarController extends Controller
{
    protected RegistrationService $registration;
    protected ClasslistService $classlists;

    public function __construct(RegistrationService $registration, ClasslistService $classlists)
    {
        $this->registration = $registration;
        $this->classlists = $classlists;
    }

    /**
     * POST /api/v1/registrar/daily-enrollment
     * Body: { syid: int, start: 'YYYY-MM-DD', end: 'YYYY-MM-DD', second_degree_iac?: [{ slug: string }] }
     * Mirrors CI registrar::daily_enrollment_report_data for tallies per day within date window.
     */
    public function dailyEnrollment(Request $request): JsonResponse
    {
        $data = $request->validate([
            'syid' => 'required|integer',
            'start' => 'required|date_format:Y-m-d',
            'end' => 'required|date_format:Y-m-d|after_or_equal:start',
            'second_degree_iac' => 'sometimes|array',
            'second_degree_iac.*.slug' => 'required|string'
        ]);

        $syid = (int) $data['syid'];
        $start = $data['start'];
        $end = $data['end'];
        $secondDegreeIac = [];
        if (!empty($data['second_degree_iac'])) {
            foreach ($data['second_degree_iac'] as $applicant) {
                if (isset($applicant['slug'])) {
                    $secondDegreeIac[] = $applicant['slug'];
                }
            }
        }

        // Maintain original 422 behavior for invalid syid
        $activeSem = DB::table('tb_mas_sy')->where('intID', $syid)->first();
        if (!$activeSem) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid syid'
            ], 422);
        }

        $result = $this->registration->getDailyEnrollment($syid, $start, $end, $secondDegreeIac);

        return response()->json($result);
    }

    /**
     * GET /api/v1/registrar/grading/meta?dept=college|shs|next
     * Mirrors CI registrar::search_grading_data
     */
    public function gradingMeta(Request $request): JsonResponse
    {
        $dept = $request->query('dept');
        if (!in_array($dept, ['college', 'shs', 'next'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid dept. Use one of: college, shs, next'
            ], 422);
        }

        $meta = $this->classlists->getGradingMeta($dept);

        return response()->json([
            'success' => true,
            'data' => $meta,
        ]);
    }

    /**
     * GET /api/v1/registrar/grading/sections?term=SYID
     * Mirrors CI registrar::search_grading_sections
     */
    public function gradingSections(Request $request): JsonResponse
    {
        $term = $request->query('term');
        if (empty($term)) {
            return response()->json([
                'success' => false,
                'message' => 'Missing term'
            ], 422);
        }

        $out = $this->classlists->getGradingSections($term);

        return response()->json([
            'success' => true,
            'data' => $out,
        ]);
    }

    /**
     * POST /api/v1/registrar/grading/results
     * Body: { term, faculty?, subject?, section?, year?, class_name?, sub_section? }
     * Mirrors CI registrar::search_grading_results
     */
    public function gradingResults(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'term' => 'required',
            'faculty' => 'nullable',
            'subject' => 'nullable',
            'section' => 'nullable',
            'year' => 'nullable',
            'class_name' => 'nullable',
            'sub_section' => 'nullable',
        ]);

        $out = $this->classlists->getGradingResults($payload);

        return response()->json([
            'success' => true,
            'data' => [
                'results' => $out['results'],
            ],
        ]);
    }

    /**
     * GET /api/v1/registrar/classlist/{id}/submitted
     * Mirrors CI registrar::submitted_grades_data
     */
    public function classlistSubmitted(int $id): JsonResponse
    {
        $out = $this->classlists->getClasslistSubmitted($id);

        if (!$out['classlist']) {
            return response()->json([
                'success' => false,
                'message' => 'Classlist not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'students' => $out['students'],
                'classlist' => $out['classlist'],
            ],
        ]);
    }
}
