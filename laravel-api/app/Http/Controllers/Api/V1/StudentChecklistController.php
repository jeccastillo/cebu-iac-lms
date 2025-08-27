<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\StudentChecklist;
use App\Models\StudentChecklistItem;
use App\Models\Subject;
use App\Services\StudentChecklistService;
use App\Services\SystemLogService;
use App\Http\Requests\Api\V1\ChecklistGenerateRequest;
use App\Http\Requests\Api\V1\ChecklistItemStoreRequest;
use App\Http\Requests\Api\V1\ChecklistItemUpdateRequest;
use App\Http\Resources\StudentChecklistResource;
use App\Http\Resources\StudentChecklistItemResource;

class StudentChecklistController extends Controller
{
    public function __construct(private StudentChecklistService $service)
    {
    }

    /**
     * GET /api/v1/students/{student}/checklist
     * Optional query: year (int), sem (string: 1st|2nd|3rd)
     */
    public function index(Request $request, int $student)
    {
        $year = $request->query('year');
        $sem  = $request->query('sem');

        $q = StudentChecklist::query()
            ->where('intStudentID', $student)
            ->with(['items.subject'])
            ->orderBy('created_at', 'desc');
        // Checklist-level year/sem columns no longer exist; filters are ignored.

        $checklist = $q->first();

        return response()->json([
            'success' => true,
            'data'    => $checklist ? (new StudentChecklistResource($checklist)) : null,
        ]);
    }

    /**
     * POST /api/v1/students/{student}/checklist/generate
     * body: intCurriculumID? (optional; fallback to user.intCurriculumID)
     */
    public function generate(ChecklistGenerateRequest $request, int $student)
    {
        $payload = $request->validated();

        // Per confirmation: use tb_mas_users.intCurriculumID by default if not supplied
        $intCurriculumID = (int)($payload['intCurriculumID'] ?? 0);
        if ($intCurriculumID <= 0) {
            $user = DB::table('tb_mas_users')->where('intID', $student)->first();
            if (!$user || !isset($user->intCurriculumID) || (int)$user->intCurriculumID <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to resolve curriculum for student.',
                ], 422);
            }
            $intCurriculumID = (int)$user->intCurriculumID;
        }

        // Generate checklist; items will inherit Year/Sem from tb_mas_curriculum_subject
        $checklist = $this->service->generateFromCurriculum($student, $intCurriculumID);

        return response()->json([
            'success' => true,
            'data'    => new StudentChecklistResource($checklist->load('items.subject')),
        ]);
    }

    /**
     * POST /api/v1/students/{student}/checklist/items
     */
    public function addItem(ChecklistItemStoreRequest $request, int $student)
    {
        $payload = $request->validated();

        $checklist = StudentChecklist::where('intID', $payload['intChecklistID'] ?? 0)
            ->where('intStudentID', $student)
            ->first();

        if (!$checklist) {
            return response()->json([
                'success' => false,
                'message' => 'Checklist not found for student.',
            ], 404);
        }

        // Ensure subject exists
        $subjectId = (int)$payload['intSubjectID'];
        $subject = Subject::where('intID', $subjectId)->first();
        if (!$subject) {
            return response()->json([
                'success' => false,
                'message' => 'Subject not found.',
            ], 404);
        }

        $item = StudentChecklistItem::create([
            'intChecklistID' => $checklist->intID,
            'intSubjectID'   => $subjectId,
            'intYearLevel'   => $payload['intYearLevel'] ?? null,
            'intSem'         => $payload['intSem'] ?? null,
            'strStatus'      => $payload['strStatus'] ?? 'planned',
            'dteCompleted'   => $payload['dteCompleted'] ?? null,
            'isRequired'     => isset($payload['isRequired']) ? (int)(bool)$payload['isRequired'] : 1,
        ]);

        // System log: item added
        SystemLogService::log(
            'create',
            'StudentChecklistItem',
            $item->intID ?? null,
            null,
            $item,
            $request
        );

        return response()->json([
            'success' => true,
            'data'    => new StudentChecklistItemResource($item->load('subject')),
        ]);
    }

    /**
     * PUT /api/v1/students/{student}/checklist/items/{item}
     */
    public function updateItem(ChecklistItemUpdateRequest $request, int $student, int $item)
    {
        $payload = $request->validated();

        $itemModel = StudentChecklistItem::where('intID', $item)->first();
        if (!$itemModel) {
            return response()->json([
                'success' => false,
                'message' => 'Checklist item not found.',
            ], 404);
        }

        $checklist = StudentChecklist::where('intID', $itemModel->intChecklistID)
            ->where('intStudentID', $student)
            ->first();

        if (!$checklist) {
            return response()->json([
                'success' => false,
                'message' => 'Checklist not found for student.',
            ], 404);
        }

        // Capture old values for logging
        $before = $itemModel->toArray();

        $itemModel->fill([
            'strStatus'    => $payload['strStatus'] ?? $itemModel->strStatus,
            'dteCompleted' => $payload['dteCompleted'] ?? $itemModel->dteCompleted,
            'isRequired'   => array_key_exists('isRequired', $payload) ? (int)(bool)$payload['isRequired'] : $itemModel->isRequired,
            'intYearLevel' => array_key_exists('intYearLevel', $payload) ? ($payload['intYearLevel'] !== null ? (int)$payload['intYearLevel'] : null) : $itemModel->intYearLevel,
            'intSem'       => array_key_exists('intSem', $payload) ? ($payload['intSem'] !== null ? (int)$payload['intSem'] : null) : $itemModel->intSem,
        ])->save();

        // Capture new values for logging
        $after = $itemModel->toArray();

        // System log: item updated
        SystemLogService::log(
            'update',
            'StudentChecklistItem',
            $itemModel->intID ?? null,
            $before,
            $after,
            $request
        );

        return response()->json([
            'success' => true,
            'data'    => new StudentChecklistItemResource($itemModel->load('subject')),
        ]);
    }

    /**
     * DELETE /api/v1/students/{student}/checklist/items/{item}
     */
    public function deleteItem(Request $request, int $student, int $item)
    {
        $itemModel = StudentChecklistItem::where('intID', $item)->first();
        if (!$itemModel) {
            return response()->json([
                'success' => false,
                'message' => 'Checklist item not found.',
            ], 404);
        }

        $checklist = StudentChecklist::where('intID', $itemModel->intChecklistID)
            ->where('intStudentID', $student)
            ->first();

        if (!$checklist) {
            return response()->json([
                'success' => false,
                'message' => 'Checklist not found for student.',
            ], 404);
        }

        // Capture old values for logging
        $before = $itemModel->toArray();

        $itemModel->delete();

        // System log: item deleted
        SystemLogService::log(
            'delete',
            'StudentChecklistItem',
            $itemModel->intID ?? null,
            $before,
            null,
            $request
        );

        return response()->json([
            'success' => true,
            'data'    => null,
        ]);
    }

    /**
     * GET /api/v1/students/{student}/checklist/summary
     * Optional query: year, sem to select checklist; else latest.
     */
    public function summary(Request $request, int $student)
    {
        $year = $request->query('year');
        $sem  = $request->query('sem');

        $q = StudentChecklist::query()
            ->where('intStudentID', $student)
            ->with(['items']);
        // Checklist-level year/sem columns no longer exist; filters are ignored.

        $checklist = $q->orderBy('created_at', 'desc')->first();

        if (!$checklist) {
            return response()->json([
                'success' => true,
                'data'    => [
                    'total'     => 0,
                    'required'  => 0,
                    'completed' => 0,
                    'remaining' => 0,
                    'percent'   => 0.0,
                ],
            ]);
        }

        $summary = $this->service->computeSummary($checklist);

        return response()->json([
            'success' => true,
            'data'    => $summary,
        ]);
    }
}
