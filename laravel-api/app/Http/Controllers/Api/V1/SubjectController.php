<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\Api\V1\SubjectSubmitRequest;

class SubjectController extends Controller
{
    /**
     * GET /api/v1/subjects
     * Query params:
     *  - search: filters by strCode or strDescription (LIKE %term%)
     *  - department: optional filter tb_mas_subjects.strDepartment
     *  - limit, page: simple pagination (default 25 per page)
     * Returns minimal fields commonly required by clients.
     */
    public function index(Request $request)
    {
        $search     = trim((string) $request->query('search', ''));
        $department = $request->query('department');
        $limit      = (int) ($request->query('limit', 25));
        $page       = max(1, (int) ($request->query('page', 1)));
        $offset     = ($page - 1) * $limit;

        $q = DB::table('tb_mas_subjects');

        if ($department) {
            $q->where('strDepartment', $department);
        }

        if ($search !== '') {
            $q->where(function ($qq) use ($search) {
                $qq->where('strCode', 'LIKE', "%{$search}%")
                   ->orWhere('strDescription', 'LIKE', "%{$search}%");
            });
        }

        $total = $q->count();

        $items = $q->orderBy('strCode', 'asc')
            ->offset($offset)
            ->limit($limit)
            ->select([
                'intID',
                'strCode',
                'strDescription',
                'strUnits',
                'intLab',
                'strDepartment',
            ])
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $items,
            'meta'    => [
                'total' => $total,
                'page'  => $page,
                'limit' => $limit,
            ],
        ]);
    }

    /**
     * GET /api/v1/subjects/{id}
     * Returns the raw subject record (CI: Data_fetcher::getSubjectPlain).
     */
    public function show($id)
    {
        $subject = DB::table('tb_mas_subjects')
            ->where('intID', $id)
            ->first();

        if (!$subject) {
            return response()->json([
                'success' => false,
                'message' => 'Subject not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $subject
        ]);
    }

    /**
     * GET /api/v1/subjects/by-curriculum
     * Query params:
     *  - curriculum (required): tb_mas_curriculum.intID
     * Mirrors Data_fetcher::get_subjects_by_course (subjects in a curriculum).
     */
    public function byCurriculum(Request $request)
    {
        $curriculum = (int) $request->query('curriculum', 0);
        if ($curriculum <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'curriculum is required'
            ], 422);
        }

        $subjects = DB::table('tb_mas_subjects')
            ->join('tb_mas_curriculum_subject', 'tb_mas_curriculum_subject.intSubjectID', '=', 'tb_mas_subjects.intID')
            ->where('tb_mas_curriculum_subject.intCurriculumID', $curriculum)
            ->orderBy('tb_mas_curriculum_subject.intYearLevel', 'asc')
            ->orderBy('tb_mas_curriculum_subject.intSem', 'asc')
            ->select([
                'tb_mas_subjects.intID',
                'tb_mas_subjects.strCode',
                'tb_mas_subjects.strDescription',
                'tb_mas_subjects.strUnits',
                'tb_mas_curriculum_subject.intYearLevel',
                'tb_mas_curriculum_subject.intSem',
            ])
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $subjects
        ]);
    }

    /**
     * POST /api/v1/subjects/submit
     * Inserts a new subject (CI Subject::submit_subject). Accepts key fields only.
     */
    public function submit(SubjectSubmitRequest $request)
    {
        $payload = $request->validated();

        $newId = DB::table('tb_mas_subjects')->insertGetId($payload);

        return response()->json([
            'success' => true,
            'message' => 'Success',
            'newid'   => (int) $newId
        ]);
    }

    /**
     * POST /api/v1/subjects/edit
     * Updates an existing subject by intID (CI Subject::edit_submit_subject)
     */
    public function edit(Request $request)
    {
        $id = (int) $request->input('intID', 0);
        if ($id <= 0) {
            return response()->json(['success' => false, 'message' => 'intID required'], 422);
        }

        $data = $request->only([
            'strCode','strDescription','strUnits','strTuitionUnits','strLabClassification','intLab',
            'strDepartment','intLectHours','intPrerequisiteID','intEquivalentID1','intEquivalentID2',
            'intProgramID','isNSTP','isThesisSubject','isInternshipSubject','include_gwa',
            'grading_system_id','grading_system_id_midterm','isElective','isSelectableElective',
            'strand','intBridging','intMajor'
        ]);

        if (empty($data)) {
            return response()->json(['success' => false, 'message' => 'No fields to update'], 422);
        }

        DB::table('tb_mas_subjects')->where('intID', $id)->update($data);

        return response()->json(['success' => true, 'message' => 'Success']);
    }

    /**
     * POST /api/v1/subjects/submit-eq
     * Replaces subject equivalents for a subject (CI Subject::submit_eq_subject)
     * Body: { intSubjectID, subj: [intEquivalentID...] }
     */
    public function submitEq(Request $request)
    {
        $subject = (int) $request->input('intSubjectID', 0);
        $subs = $request->input('subj', []);
        if ($subject <= 0) {
            return response()->json(['success' => false, 'message' => 'intSubjectID required'], 422);
        }

        DB::table('tb_mas_equivalents')->where('intSubjectID', $subject)->delete();

        if (is_array($subs)) {
            foreach ($subs as $subj) {
                DB::table('tb_mas_equivalents')->insert([
                    'intEquivalentID' => (int) $subj,
                    'intSubjectID'    => $subject
                ]);
            }
        }

        return response()->json(['success' => true, 'message' => 'Success']);
    }

    /**
     * POST /api/v1/subjects/submit-days
     * Replaces teaching days for a subject (CI Subject::submit_days_subject)
     * Body: { intSubjectID, subj: ['1 3','2 4', ...] }
     */
    public function submitDays(Request $request)
    {
        $subject = (int) $request->input('intSubjectID', 0);
        $days = $request->input('subj', []);
        if ($subject <= 0) {
            return response()->json(['success' => false, 'message' => 'intSubjectID required'], 422);
        }

        DB::table('tb_mas_days')->where('intSubjectID', $subject)->delete();

        if (is_array($days)) {
            foreach ($days as $d) {
                DB::table('tb_mas_days')->insert([
                    'strDays'      => (string) $d,
                    'intSubjectID' => $subject
                ]);
            }
        }

        return response()->json(['success' => true, 'message' => 'Success']);
    }

    /**
     * POST /api/v1/subjects/submit-room
     * Replaces room preferences for a subject (CI Subject::submit_room_subject)
     * Body: { intSubjectID, rooms: [roomId...] }
     */
    public function submitRoom(Request $request)
    {
        $subject = (int) $request->input('intSubjectID', 0);
        $rooms = $request->input('rooms', []);
        if ($subject <= 0) {
            return response()->json(['success' => false, 'message' => 'intSubjectID required'], 422);
        }

        DB::table('tb_mas_room_subject')->where('intSubjectID', $subject)->delete();

        if (is_array($rooms)) {
            foreach ($rooms as $room) {
                DB::table('tb_mas_room_subject')->insert([
                    'intRoomID'    => (int) $room,
                    'intSubjectID' => $subject
                ]);
            }
        }

        return response()->json(['success' => true, 'message' => 'Success']);
    }

    /**
     * POST /api/v1/subjects/submit-prereq
     * Adds a prerequisite row (CI Subject::submit_prereq_subject)
     * Body: { intSubjectID, program, intPrerequisiteID }
     */
    public function submitPrereq(Request $request)
    {
        $subject = (int) $request->input('intSubjectID', 0);
        $program = $request->input('program');
        $pre     = (int) $request->input('intPrerequisiteID', 0);

        if ($subject <= 0 || $pre <= 0) {
            return response()->json(['success' => false, 'message' => 'intSubjectID and intPrerequisiteID required'], 422);
        }

        DB::table('tb_mas_prerequisites')->insert([
            'program'        => $program,
            'intPrerequisiteID' => $pre,
            'intSubjectID'   => $subject,
        ]);

        return response()->json(['success' => true, 'message' => 'Success']);
    }

    /**
     * POST /api/v1/subjects/delete-prereq
     * Deletes a prerequisite by id (CI Subject::delete_prereq)
     * Body: { id }
     */
    public function deletePrereq(Request $request)
    {
        $id = (int) $request->input('id', 0);
        if ($id <= 0) {
            return response()->json(['success' => false, 'message' => 'id required'], 422);
        }

        DB::table('tb_mas_prerequisites')->where('intID', $id)->delete();

        return response()->json(['success' => true, 'message' => 'Success']);
    }

    /**
     * POST /api/v1/subjects/delete
     * Deletes a subject by id (CI Subject::delete_subject)
     * Body: { id }
     */
    public function delete(Request $request)
    {
        $id = (int) $request->input('id', 0);
        if ($id <= 0) {
            return response()->json(['success' => false, 'message' => 'id required'], 422);
        }
        // Simple delete; in CI it goes through data_poster->deleteSubject (may enforce constraints/logging)
        DB::table('tb_mas_subjects')->where('intID', $id)->delete();

        return response()->json(['success' => true, 'message' => 'success']);
    }
}
