<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use App\Services\SystemLogService;

class InitialRequirementsAdminController extends Controller
{
    /**
     * POST /api/v1/admissions/initial-requirements/{student}/upload/{appReqId}
     *
     * Admissions/Admin-only endpoint to upload or replace an initial-requirement file
     * on behalf of the applicant. Mirrors validation and storage behavior of the
     * public endpoint, but records a SystemLog entry with admin user context.
     *
     * Field: file (multipart)
     */
    public function upload(int $student, int $appReqId, Request $request): JsonResponse
    {
        // Guard: ensure target student exists (tb_mas_users)
        $user = DB::table('tb_mas_users')->where('intID', $student)->first();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found.',
            ], 404);
        }

        // Locate requirement row and validate ownership
        $appReq = DB::table('tb_mas_application_requirements')
            ->where('intID', $appReqId)
            ->first();

        if (!$appReq || (int) $appReq->intStudentID !== (int) $student) {
            return response()->json([
                'success' => false,
                'message' => 'Requirement not found for this student.',
            ], 404);
        }

        // Validate file: only PDF, Excel, Images up to 10MB (parity with public endpoint)
        $validator = Validator::make($request->all(), [
            'file' => [
                'required',
                'file',
                'mimes:pdf,jpg,jpeg,png,gif,webp,xls,xlsx,csv',
                'max:10240', // 10MB
            ],
        ], [
            'file.required' => 'Please select a file to upload.',
            'file.mimes'    => 'Only PDF, Excel (xls, xlsx, csv), and image files (jpg, jpeg, png, gif, webp) are allowed.',
            'file.max'      => 'File size must not exceed 10MB.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first('file'),
                'errors'  => $validator->errors(),
            ], 422);
        }

        $file = $request->file('file');

        // Snapshot old values for system log
        $oldSnapshot = [
            'submitted_status' => (bool) ($appReq->submitted_status ?? false),
            'file_link'        => $appReq->file_link ?? null,
        ];

        // Store file under public disk, namespaced per student
        $ext = $file->getClientOriginalExtension();
        $safeName = Str::uuid()->toString() . ($ext ? ('.' . strtolower($ext)) : '');
        $dir = 'initial-requirements/' . $student;
        $path = $file->storeAs($dir, $safeName, ['disk' => 'public']);

        // Update record
        DB::table('tb_mas_application_requirements')
            ->where('intID', $appReqId)
            ->update([
                'file_link'       => $path, // relative path; normalize for consumers if needed
                'submitted_status'=> true,
                'updated_at'      => now(),
            ]);

        // Prepare success payload (optionally include requirement name/type/description)
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

        // If available, compute the public file URL using latest applicant_data hash for this student
        try {
            $appData = DB::table('tb_mas_applicant_data')
                ->where('user_id', $student)
                ->orderByDesc('id')
                ->first();
            if ($updated && $appData && !empty($updated->file_link) && !empty($appData->hash)) {
                $updated->file_link = url('/api/v1/public/initial-requirements/' . $appData->hash . '/file/' . $appReqId);
            }
        } catch (\Throwable $e) {
            // leave relative path if any error occurs
        }

        // System Log entry for audit
        try {
            $newSnapshot = [
                'submitted_status' => true,
                'file_link'        => $path,
            ];
            SystemLogService::log(
                'update',
                'ApplicationRequirement',
                (int) $appReqId,
                $oldSnapshot,
                $newSnapshot,
                $request
            );
        } catch (\Throwable $e) {
            // swallow logging failures
        }

        return response()->json([
            'success' => true,
            'message' => 'File uploaded successfully.',
            'data'    => $updated,
        ]);
    }
}
