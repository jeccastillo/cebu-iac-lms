<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Clinic\AttachmentResource;
use App\Models\ClinicAttachment;
use App\Models\ClinicVisit;
use App\Services\ClinicVisitService;
use App\Services\SystemLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class ClinicAttachmentController extends Controller
{
    protected ClinicVisitService $visitService;

    public function __construct(ClinicVisitService $visitService)
    {
        $this->visitService = $visitService;
    }

    /**
     * GET /api/v1/clinic/attachments?record_id=... or ?visit_id=...&page=&per_page=
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['record_id', 'visit_id']);
        $page = (int) $request->query('page', 1);
        $perPage = (int) $request->query('per_page', 20);

        $paginator = $this->visitService->listAttachments($filters, $page, $perPage);

        $data = [];
        foreach ($paginator->items() as $item) {
            $data[] = (new AttachmentResource($item))->toArray($request);
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
     * POST /api/v1/clinic/attachments (multipart/form-data)
     * Fields:
     * - file: required
     * - record_id: required if visit_id not provided
     * - visit_id: optional, associates file to a visit
     */
    public function store(Request $request): JsonResponse
    {
        $maxMb = (int) env('CLINIC_MAX_UPLOAD_MB', 10);
        $maxKb = $maxMb * 1024;

        $request->validate([
            'file' => "required|file|max:{$maxKb}|mimetypes:application/pdf,image/jpeg,image/png,image/jpg",
            'record_id' => 'required_without:visit_id|nullable|integer|min:1',
            'visit_id' => 'nullable|integer|min:1',
        ]);

        $file = $request->file('file');
        if (!$file) {
            return response()->json([
                'success' => false,
                'message' => 'No file uploaded.'
            ], 422);
        }

        $recordId = $request->input('record_id');
        $visitId = $request->input('visit_id');

        // If only visit_id provided, infer record_id from visit
        if (!$recordId && $visitId) {
            $visit = ClinicVisit::find($visitId);
            if ($visit) {
                $recordId = $visit->record_id;
            }
        }

        if (!$recordId) {
            return response()->json([
                'success' => false,
                'message' => 'record_id is required when visit_id is not provided or invalid.'
            ], 422);
        }

        $original = $file->getClientOriginalName();
        $safeName = time() . '_' . preg_replace('/[^\w\.\-]+/u', '_', $original);

        $dir = 'clinic/' . (int) $recordId;
        if ($visitId) {
            $dir .= '/visit_' . (int) $visitId;
        }

        // Store on public disk
        $storedPath = Storage::disk('public')->putFileAs($dir, $file, $safeName);

        $attachment = $this->visitService->addAttachment([
            'record_id'     => (int) $recordId,
            'visit_id'      => $visitId ? (int) $visitId : null,
            'original_name' => $original,
            'path'          => $storedPath, // path relative to 'public' disk
            'mime'          => $file->getMimeType() ?: 'application/octet-stream',
            'size_bytes'    => (int) $file->getSize(),
            'uploaded_by'   => $request->input('uploaded_by'),
        ]);

        SystemLogService::log('create', 'ClinicAttachment', $attachment->id, null, $attachment, $request);

        return response()->json([
            'success' => true,
            'data' => new AttachmentResource($attachment),
        ]);
    }

    /**
     * GET /api/v1/clinic/attachments/{id}/download
     */
    public function download(int $id)
    {
        $attachment = ClinicAttachment::find($id);
        if (!$attachment) {
            return response()->json([
                'success' => false,
                'message' => 'Attachment not found.'
            ], 404);
        }

        if (!Storage::disk('public')->exists($attachment->path)) {
            return response()->json([
                'success' => false,
                'message' => 'File not found on storage.'
            ], 404);
        }

        return Storage::disk('public')->download($attachment->path, $attachment->original_name);
    }

    /**
     * DELETE /api/v1/clinic/attachments/{id}
     */
    public function destroy(int $id, Request $request): JsonResponse
    {
        $attachment = ClinicAttachment::find($id);
        if (!$attachment) {
            return response()->json([
                'success' => false,
                'message' => 'Attachment not found.'
            ], 404);
        }

        // Remove file from storage if present
        if (!empty($attachment->path) && Storage::disk('public')->exists($attachment->path)) {
            Storage::disk('public')->delete($attachment->path);
        }

        $old = clone $attachment;
        $visitId = $attachment->visit_id;

        $attachment->delete();

        // Sync attachments_count for associated visit
        if ($visitId) {
            $count = ClinicAttachment::where('visit_id', $visitId)->count();
            ClinicVisit::where('id', $visitId)->update(['attachments_count' => $count]);
        }

        SystemLogService::log('delete', 'ClinicAttachment', $id, $old, null, $request);

        return response()->json([
            'success' => true
        ]);
    }
}
