<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PortalController extends Controller
{
    /**
     * POST /api/v1/portal/save-token
     * Body: { email: string, token: string }
     * Behavior matches CI PortalApi::save_token
     */
    public function saveToken(Request $request)
    {
        $email = $request->input('email');
        $token = $request->input('token');

        if (!$email || !$token) {
            return response()->json([
                'success' => false,
                'message' => 'Email and token are required'
            ], 422);
        }

        $user = DB::table('tb_mas_users')->where('strEmail', $email)->first();

        if ($user) {
            DB::table('tb_mas_users')
                ->where('intID', $user->intID)
                ->update(['strGSuiteEmail' => $token]);

            return response()->json([
                'success' => true,
                'message' => 'Successfully saved token'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Error email does not exist'
        ], 404);
    }

    /**
     * GET /api/v1/portal/active-programs
     * Behavior matches CI PortalApi::view_active_programs
     */
    public function activePrograms(Request $request)
    {
        $programs = DB::table('tb_mas_programs')
            ->where('enumEnabled', 1)
            ->select([
                'intProgramID as id',
                'strProgramDescription as title',
                'type',
                'strMajor',
            ])
            ->orderBy('strProgramDescription')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $programs
        ]);
    }

    /**
     * POST /api/v1/portal/student-data
     * Body: { token: string }
     * Behavior matches CI PortalApi::student_data
     */
    public function studentData(Request $request)
    {
        $token = $request->input('token');

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'token is required'
            ], 422);
        }

        // Find user by strGSuiteEmail (token), include program code
        $user = DB::table('tb_mas_users')
            ->join('tb_mas_programs', 'tb_mas_users.intProgramID', '=', 'tb_mas_programs.intProgramID')
            ->where('strGSuiteEmail', $token)
            ->select('tb_mas_users.*', 'tb_mas_programs.strProgramCode')
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false
            ]);
        }

        // Latest registration with non-null dteRegistered
        $registered = DB::table('tb_mas_registration')
            ->join('tb_mas_sy', 'tb_mas_registration.intAYID', '=', 'tb_mas_sy.intID')
            ->where('intStudentID', $user->intID)
            ->whereNotNull('dteRegistered')
            ->orderBy('dteRegistered', 'desc')
            ->select(
                'tb_mas_registration.*',
                'tb_mas_sy.enumSem',
                'tb_mas_sy.strYearStart',
                'tb_mas_sy.strYearEnd'
            )
            ->first();

        if (!$registered) {
            return response()->json([
                'success' => false
            ]);
        }

        $payload = [
            'first_name'     => $user->strFirstname,
            'last_name'      => $user->strLastname,
            'personal_email' => $user->strEmail,
            'student_number' => $user->strStudentNumber,
            'contact_number' => $user->strMobileNumber,
            'course_id'      => $user->intProgramID,
            'course_name'    => $user->strProgramCode,
            'last_term'      => $registered->enumSem . ' Term',
            'last_term_sy'   => $registered->strYearStart . '-' . $registered->strYearEnd,
        ];

        return response()->json([
            'success' => true,
            'data' => $payload
        ]);
    }
}
