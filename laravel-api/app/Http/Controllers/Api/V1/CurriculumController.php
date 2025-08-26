<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\Api\V1\CurriculumUpsertRequest;
use App\Http\Resources\CurriculumResource;
use App\Http\Resources\CurriculumSubjectResource;

class CurriculumController extends Controller
{
    /**
     * GET /api/v1/curriculum
     * Lists all curricula with basic information
     * Query params:
     *  - search: filters by strCurriculum (LIKE %term%)
     *  - program_id: optional filter by intProgramID
     *  - limit, page: simple pagination (default 25 per page)
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $programId = $request->query('program_id');
        $limit = (int) ($request->query('limit', 25));
        $page = max(1, (int) ($request->query('page', 1)));
        $offset = ($page - 1) * $limit;

        $q = DB::table('tb_mas_curriculum');

        if ($programId) {
            $q->where('intProgramID', $programId);
        }

        if ($search !== '') {
            $q->where('strName', 'LIKE', "%{$search}%");
        }

        $total = $q->count();

        $items = $q->orderBy('strName', 'asc')
            ->offset($offset)
            ->limit($limit)
            ->select([
                'intID',
                'strName',
                'intProgramID',
                'active',
                'isEnhanced'
            ])
            ->get();

        return response()->json([
            'success' => true,
            'data' => CurriculumResource::collection($items),
            'meta' => [
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
            ],
        ]);
    }

    /**
     * GET /api/v1/curriculum/{id}
     * Returns a specific curriculum with its details
     */
    public function show($id)
    {
        $curriculum = DB::table('tb_mas_curriculum')
            ->where('intID', $id)
            ->first();

        if (!$curriculum) {
            return response()->json([
                'success' => false,
                'message' => 'Curriculum not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new CurriculumResource($curriculum)
        ]);
    }

    /**
     * GET /api/v1/curriculum/{id}/subjects
     * Returns subjects associated with a curriculum
     * Mirrors Data_fetcher::get_subjects_by_course functionality
     */
    public function subjects($id)
    {
        $subjects = DB::table('tb_mas_subjects')
            ->join('tb_mas_curriculum_subject', 'tb_mas_curriculum_subject.intSubjectID', '=', 'tb_mas_subjects.intID')
            ->where('tb_mas_curriculum_subject.intCurriculumID', $id)
            ->orderBy('tb_mas_curriculum_subject.intYearLevel', 'asc')
            ->orderBy('tb_mas_curriculum_subject.intSem', 'asc')
            ->select([
                'tb_mas_subjects.intID',
                'tb_mas_subjects.strCode',
                'tb_mas_subjects.strDescription',
                'tb_mas_subjects.strUnits',
                'tb_mas_subjects.intLab',
                'tb_mas_curriculum_subject.intYearLevel',
                'tb_mas_curriculum_subject.intSem',
                'tb_mas_curriculum_subject.intID as curriculum_subject_id'
            ])
            ->get();

        return response()->json([
            'success' => true,
            'data' => CurriculumSubjectResource::collection($subjects)
        ]);
    }

    /**
     * POST /api/v1/curriculum
     * Creates a new curriculum
     */
    public function store(CurriculumUpsertRequest $request)
    {
        $data = $request->validated();

        $newId = DB::table('tb_mas_curriculum')->insertGetId($data);
        $created = DB::table('tb_mas_curriculum')->where('intID', $newId)->first();

        return response()->json([
            'success' => true,
            'message' => 'Curriculum created successfully',
            'newid' => (int) $newId,
            'data' => new CurriculumResource($created),
        ], 201);
    }

    /**
     * PUT /api/v1/curriculum/{id}
     * Updates an existing curriculum
     */
    public function update(CurriculumUpsertRequest $request, $id)
    {
        $curriculum = DB::table('tb_mas_curriculum')->where('intID', $id)->first();
        
        if (!$curriculum) {
            return response()->json([
                'success' => false,
                'message' => 'Curriculum not found'
            ], 404);
        }

        $data = $request->validated();

        if (empty($data)) {
            return response()->json([
                'success' => false,
                'message' => 'No fields to update'
            ], 422);
        }

        DB::table('tb_mas_curriculum')->where('intID', $id)->update($data);

        $updated = DB::table('tb_mas_curriculum')->where('intID', $id)->first();

        return response()->json([
            'success' => true,
            'message' => 'Curriculum updated successfully',
            'data' => new CurriculumResource($updated),
        ]);
    }

    /**
     * DELETE /api/v1/curriculum/{id}
     * Deletes a curriculum
     */
    public function destroy($id)
    {
        $curriculum = DB::table('tb_mas_curriculum')->where('intID', $id)->first();
        
        if (!$curriculum) {
            return response()->json([
                'success' => false,
                'message' => 'Curriculum not found'
            ], 404);
        }

        // Check if curriculum has associated subjects
        $hasSubjects = DB::table('tb_mas_curriculum_subject')
            ->where('intCurriculumID', $id)
            ->exists();

        if ($hasSubjects) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete curriculum with associated subjects'
            ], 422);
        }

        DB::table('tb_mas_curriculum')->where('intID', $id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Curriculum deleted successfully'
        ]);
    }

    /**
     * POST /api/v1/curriculum/{id}/subjects
     * Adds a subject to a curriculum
     */
    public function addSubject(Request $request, $id)
    {
        $data = $request->validate([
            'intSubjectID' => 'required|integer',
            'intYearLevel' => 'required|integer|min:1|max:10',
            'intSem' => 'required|integer|min:1|max:3'
        ]);

        // Check if curriculum exists
        $curriculum = DB::table('tb_mas_curriculum')->where('intID', $id)->first();
        if (!$curriculum) {
            return response()->json([
                'success' => false,
                'message' => 'Curriculum not found'
            ], 404);
        }

        // Check if subject exists
        $subject = DB::table('tb_mas_subjects')->where('intID', $data['intSubjectID'])->first();
        if (!$subject) {
            return response()->json([
                'success' => false,
                'message' => 'Subject not found'
            ], 404);
        }

        // Check if association already exists
        $exists = DB::table('tb_mas_curriculum_subject')
            ->where('intCurriculumID', $id)
            ->where('intSubjectID', $data['intSubjectID'])
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Subject already associated with this curriculum'
            ], 422);
        }

        $data['intCurriculumID'] = $id;
        $newId = DB::table('tb_mas_curriculum_subject')->insertGetId($data);

        return response()->json([
            'success' => true,
            'message' => 'Subject added to curriculum successfully',
            'newid' => (int) $newId
        ], 201);
    }

    /**
     * DELETE /api/v1/curriculum/{id}/subjects/{subjectId}
     * Removes a subject from a curriculum
     */
    public function removeSubject($id, $subjectId)
    {
        $deleted = DB::table('tb_mas_curriculum_subject')
            ->where('intCurriculumID', $id)
            ->where('intSubjectID', $subjectId)
            ->delete();

        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => 'Subject not found in curriculum'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Subject removed from curriculum successfully'
        ]);
    }
}
