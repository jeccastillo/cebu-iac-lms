<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

class PublicInitialRequirementsController extends Controller
{
    /**
     * GET /api/v1/public/initial-requirements/{hash}
     *
     * Resolves the applicant by hash, seeds initial requirements if missing
     * based on tb_mas_requirements.type matching the user's level (student_type),
     * then returns the application requirements list for the student.
     */
    public function index(string $hash): JsonResponse
    {
        $appData = DB::table('tb_mas_applicant_data')
            ->where('hash', $hash)
            ->orderByDesc('id')
            ->first();

        if (!$appData) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid link or applicant not found.',
            ], 404);
        }

        $studentId = (int) $appData->user_id;

        $user = DB::table('tb_mas_users')->where('intID', $studentId)->first();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found for this link.',
            ], 404);
        }

        $level = $this->normalizeLevel($user->student_type ?? null);

        $this->seedInitialRequirementsIfEmpty($studentId, $level);

        $select = [
            'ar.intID as app_req_id',
            'ar.tb_mas_requirements_id as requirement_id',
            'r.name',
            'r.type',
        ];
        if (Schema::hasColumn('tb_mas_requirements', 'description')) {
            $select[] = DB::raw("COALESCE(r.description, '') as description");
        } else {
            $select[] = DB::raw("'' as description");
        }
        $select[] = 'ar.submitted_status';
        $select[] = 'ar.file_link';

        $rows = DB::table('tb_mas_application_requirements as ar')
            ->join('tb_mas_requirements as r', 'r.intID', '=', 'ar.tb_mas_requirements_id')
            ->where('ar.intStudentID', $studentId)
            ->select($select)
            ->orderBy('r.name')
            ->get()
            ->map(function ($row) use ($hash) {
                if (!empty($row->file_link)) {
                    // Always use the file endpoint for consistency (avoid raw storage URLs)
                    $row->file_link = url('/api/v1/public/initial-requirements/' . $hash . '/file/' . $row->app_req_id);
                }
                return $row;
            });

        return response()->json([
            'success' => true,
            'data' => [
                'student' => [
                    'id' => $studentId,
                    'first_name' => $user->strFirstname ?? null,
                    'last_name' => $user->strLastname ?? null,
                    'email' => $user->strEmail ?? null,
                    'student_type' => $user->student_type ?? null,
                ],
                'requirements' => $rows,
            ],
        ]);
    }

    /**
     * POST /api/v1/public/initial-requirements/{hash}/upload/{appReqId}
     *
     * Accepts a file (key: file) and stores it. Only accepts PDF, Excel, and image files.
     * Updates the application requirement with file_link and submitted_status = 1.
     */
    public function upload(string $hash, int $appReqId, Request $request): JsonResponse
    {
        $appData = DB::table('tb_mas_applicant_data')
            ->where('hash', $hash)
            ->orderByDesc('id')
            ->first();

        if (!$appData) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid link or applicant not found.',
            ], 404);
        }

        $studentId = (int) $appData->user_id;

        $appReq = DB::table('tb_mas_application_requirements')
            ->where('intID', $appReqId)
            ->first();

        if (!$appReq || (int) $appReq->intStudentID !== $studentId) {
            return response()->json([
                'success' => false,
                'message' => 'Requirement not found for this student.',
            ], 404);
        }

        // Validate file: only PDF, Excel, Images
        // Using mimes for broad compatibility
        $validator = Validator::make($request->all(), [
            'file' => [
                'required',
                'file',
                'mimes:pdf,jpg,jpeg,png,gif,webp,xls,xlsx,csv',
                'max:10240' // 10MB
            ],
        ], [
            'file.required' => 'Please select a file to upload.',
            'file.mimes' => 'Only PDF, Excel (xls, xlsx, csv), and image files (jpg, jpeg, png, gif, webp) are allowed.',
            'file.max' => 'File size must not exceed 10MB.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first('file'),
                'errors' => $validator->errors(),
            ], 422);
        }

        $file = $request->file('file');

        $ext = $file->getClientOriginalExtension();
        $safeName = Str::uuid()->toString() . ($ext ? ('.' . strtolower($ext)) : '');

        $dir = 'initial-requirements/' . $studentId;
        $path = $file->storeAs($dir, $safeName, ['disk' => 'public']);

        // Store relative path in DB; URL will be generated via a controller endpoint
        DB::table('tb_mas_application_requirements')
            ->where('intID', $appReqId)
            ->update([
                'file_link' => $path,
                'submitted_status' => true,
                'updated_at' => now(),
            ]);

        $select = [
            'ar.intID as app_req_id',
            'ar.tb_mas_requirements_id as requirement_id',
            'r.name',
            'r.type',
        ];
        if (Schema::hasColumn('tb_mas_requirements', 'description')) {
            $select[] = DB::raw("COALESCE(r.description, '') as description");
        } else {
            $select[] = DB::raw("'' as description");
        }
        $select[] = 'ar.submitted_status';
        $select[] = 'ar.file_link';

        $updated = DB::table('tb_mas_application_requirements as ar')
            ->join('tb_mas_requirements as r', 'r.intID', '=', 'ar.tb_mas_requirements_id')
            ->where('ar.intID', $appReqId)
            ->select($select)
            ->first();

        // Normalize file_link to API file endpoint for consistent access
        if ($updated && !empty($updated->file_link)) {
            $updated->file_link = url('/api/v1/public/initial-requirements/' . $hash . '/file/' . $appReqId);
        }

        // Journey log: Student Submitted [Requirement Name]
        try {
            if ($updated && isset($updated->name)) {
                app(\App\Services\ApplicantJourneyService::class)
                    ->log((int) $appData->id, 'Student Submitted ' . $updated->name);
            }
        } catch (\Throwable $e) {
            // ignore logging failures
        }

        return response()->json([
            'success' => true,
            'message' => 'File uploaded successfully.',
            'data' => $updated,
        ]);
    }

    /**
     * If no application requirements exist for the student, seed them based on initial tb_mas_requirements
     * filtered by the user's level (type).
     */
    protected function seedInitialRequirementsIfEmpty(int $studentId, ?string $level): void
    {
        // Ensure all initial requirements for the student's level exist.
        // 1) Fetch master requirement IDs for the applicable level
        $q = DB::table('tb_mas_requirements')
            ->where('is_initial_requirements', true);

        if ($level !== null) {
            $q->where('type', $level);
        }

        $masterIds = $q->pluck('intID')->map(fn($id) => (int) $id)->all();

        if (empty($masterIds)) {
            return;
        }

        // 2) Fetch existing application requirement links for this student
        $existingIds = DB::table('tb_mas_application_requirements')
            ->where('intStudentID', $studentId)
            ->pluck('tb_mas_requirements_id')
            ->map(fn($id) => (int) $id)
            ->all();

        // 3) Compute missing requirement IDs
        $missing = array_values(array_diff($masterIds, $existingIds));

        if (empty($missing)) {
            return;
        }

        // 4) Insert missing rows
        $now = now();
        $payload = [];
        foreach ($missing as $reqId) {
            $payload[] = [
                'intStudentID' => $studentId,
                'tb_mas_requirements_id' => (int) $reqId,
                'submitted_status' => false,
                'file_link' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Insert, ignore duplicates if any race condition occurs
        try {
            DB::table('tb_mas_application_requirements')->insert($payload);
        } catch (\Throwable $e) {
            // ignore duplicate race conditions
        }
    }

    /**
     * Serve uploaded initial-requirement file by app requirement id + hash ownership.
     */
    public function file(string $hash, int $appReqId)
    {
        $appData = DB::table('tb_mas_applicant_data')
            ->where('hash', $hash)
            ->orderByDesc('id')
            ->first();

        if (!$appData) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid link or applicant not found.',
            ], 404);
        }

        $studentId = (int) $appData->user_id;

        $appReq = DB::table('tb_mas_application_requirements')
            ->where('intID', $appReqId)
            ->first();

        if (!$appReq || (int) $appReq->intStudentID !== $studentId) {
            return response()->json([
                'success' => false,
                'message' => 'Requirement not found for this student.',
            ], 404);
        }

        $path = $appReq->file_link ?? null;
        if (!$path) {
            return response()->json([
                'success' => false,
                'message' => 'No file uploaded for this requirement.',
            ], 404);
        }

        // Support both relative 'initial-requirements/...' and absolute '.../storage/...'
        $relative = $path;

        // If absolute URL, try to extract portion after '/storage/'
        if (Str::startsWith($relative, ['http://', 'https://'])) {
            $parts = parse_url($relative);
            $p = $parts['path'] ?? '';
            // Expect '/storage/dir/file.ext'
            if (Str::startsWith($p, '/')) {
                $p = ltrim($p, '/');
            }
            if (Str::startsWith($p, 'storage/')) {
                $relative = substr($p, strlen('storage/'));
            } else {
                // Fallback: keep as-is (will likely 404 below)
                $relative = $p;
            }
        } elseif (Str::startsWith($relative, ['/storage/', 'storage/'])) {
            $relative = ltrim($relative, '/');
            $relative = substr($relative, strlen('storage/'));
        } else {
            // keep as is (already a relative path stored via disk('public'))
        }

        // Ensure file exists on public disk
        if (!Storage::disk('public')->exists($relative)) {
            return response()->json([
                'success' => false,
                'message' => 'File not found.',
            ], 404);
        }

        $fullPath = Storage::disk('public')->path($relative);

        return response()->file($fullPath);
    }

    /**
     * Normalize student_type to requirements.type ('college','shs','grad').
     */
    protected function normalizeLevel(?string $studentType): ?string
    {
        if (!$studentType) return null;
        $t = strtolower(trim($studentType));

        if (in_array($t, ['college', 'shs', 'grad'], true)) {
            return $t;
        }

        // Common aliases
        if (in_array($t, ['senior high', 'senior_high', 'seniorhigh', 'k-12', 'k12', 'sh'], true)) {
            return 'shs';
        }
        if (in_array($t, ['graduate', 'masters', 'postgrad', 'post_grad'], true)) {
            return 'grad';
        }

        return null;
    }
}
