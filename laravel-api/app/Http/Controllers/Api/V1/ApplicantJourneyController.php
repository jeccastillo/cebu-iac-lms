<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApplicantJourneyController extends Controller
{
    /**
     * GET /api/v1/admissions/applicant-data/{applicantDataId}/journey
     * Returns applicant journey logs ordered by log_date ASC.
     * Optional pagination: ?page=1&amp;perPage=50
     */
    public function index(int $applicantDataId, Request $request): JsonResponse
    {
        $page = $request->has('page') ? max(1, (int) $request->input('page', 1)) : null;
        $perPage = $request->has('page') ? max(1, min(200, (int) $request->input('perPage', 50))) : null;

        $q = DB::table('tb_mas_applicant_journey')
            ->where('applicant_data_id', $applicantDataId)
            ->orderBy('log_date', 'asc')
            ->orderBy('id', 'asc');

        if ($page !== null && $perPage !== null) {
            $total = (int) $q->count();
            $rows = $q->forPage($page, $perPage)->get();

            return response()->json([
                'success' => true,
                'data' => $rows,
                'meta' => [
                    'page' => $page,
                    'perPage' => $perPage,
                    'total' => $total
                ],
            ]);
        }

        $rows = $q->get();

        return response()->json([
            'success' => true,
            'data' => $rows,
        ]);
    }
}
