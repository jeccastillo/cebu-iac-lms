<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\Api\V1\TuitionYearAddRequest;

class TuitionYearController extends Controller
{
    /**
     * GET /api/v1/tuition-years
     * Optional query:
     *  - default=1 (only default college year)
     *  - defaultShs=1 (only default shs year)
     */
    public function index(Request $request)
    {
        $q = DB::table('tb_mas_tuition_year');

        if ($request->boolean('default')) {
            $q->where('isDefault', 1);
        }

        if ($request->boolean('defaultShs')) {
            $q->where('isDefaultShs', 1);
        }

        $items = $q->orderBy('year', 'asc')->get();

        return response()->json([
            'success' => true,
            'data'    => $items,
        ]);
    }

    /**
     * GET /api/v1/tuition-years/{id}
     * Returns a single tuition year row.
     */
    public function show($id)
    {
        $item = DB::table('tb_mas_tuition_year')->where('intID', $id)->first();

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Tuition year not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $item,
        ]);
    }

    /**
     * GET /api/v1/tuition-years/{id}/misc
     * Returns misc fees (tb_mas_tuition_year_misc).
     */
    public function misc($id)
    {
        $data = DB::table('tb_mas_tuition_year_misc')
            ->where('tuitionYearID', $id)
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }

    /**
     * GET /api/v1/tuition-years/{id}/lab-fees
     * Returns lab fees (tb_mas_tuition_year_lab_fee).
     */
    public function labFees($id)
    {
        $data = DB::table('tb_mas_tuition_year_lab_fee')
            ->where('tuitionYearID', $id)
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }

    /**
     * GET /api/v1/tuition-years/{id}/installments
     * Returns active installment plans (tb_mas_tuition_year_installment).
     */
    public function installments($id)
    {
        $data = DB::table('tb_mas_tuition_year_installment')
            ->where('tuitionyear_id', $id)
            ->where('is_active', 1)
            ->orderBy('sort_order')
            ->orderBy('code')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }

    /**
     * GET /api/v1/tuition-years/{id}/tracks
     * Returns track/program-specific rates (tb_mas_tuition_year_track joined to tb_mas_programs).
     */
    public function tracks($id)
    {
        $data = DB::table('tb_mas_tuition_year_track')
            ->join('tb_mas_programs', 'tb_mas_programs.intProgramID', '=', 'tb_mas_tuition_year_track.track_id')
            ->where('tuitionyear_id', $id)
            ->select([
                'tb_mas_tuition_year_track.*',
                'tb_mas_programs.intProgramID',
                'tb_mas_programs.strProgramDescription',
                'tb_mas_programs.strProgramCode',
                'tb_mas_programs.type',
                'tb_mas_programs.strMajor',
            ])
            ->orderBy('tb_mas_programs.strProgramDescription')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }

    /**
     * GET /api/v1/tuition-years/{id}/programs
     * Returns per-program college unit rates (tb_mas_tuition_year_program joined to tb_mas_programs).
     */
    public function programs($id)
    {
        $data = DB::table('tb_mas_tuition_year_program')
            ->join('tb_mas_programs', 'tb_mas_programs.intProgramID', '=', 'tb_mas_tuition_year_program.track_id')
            ->where('tuitionyear_id', $id)
            ->select([
                'tb_mas_tuition_year_program.*',
                'tb_mas_programs.intProgramID',
                'tb_mas_programs.strProgramDescription',
                'tb_mas_programs.strProgramCode',
                'tb_mas_programs.type',
                'tb_mas_programs.strMajor',
            ])
            ->orderBy('tb_mas_programs.strProgramDescription')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }

    /**
     * GET /api/v1/tuition-years/{id}/electives
     * Returns elective subject rates (tb_mas_tuition_year_elective joined to tb_mas_subjects).
     */
    public function electives($id)
    {
        $data = DB::table('tb_mas_tuition_year_elective')
            ->join('tb_mas_subjects', 'tb_mas_subjects.intID', '=', 'tb_mas_tuition_year_elective.subject_id')
            ->where('tuitionyear_id', $id)
            ->select([
                'tb_mas_tuition_year_elective.*',
                'tb_mas_subjects.intID as subjectID',
                'tb_mas_subjects.strCode',
                'tb_mas_subjects.strDescription',
                'tb_mas_subjects.strUnits',
                'tb_mas_subjects.intLab',
            ])
            ->orderBy('tb_mas_subjects.strCode')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }

    /**
     * POST /api/v1/tuition-years/add
     * Creates a tuition year row; returns new id.
     * Body: minimally expects 'year'. Fills safe defaults for non-null numeric fields in legacy schema.
     */
    public function add(TuitionYearAddRequest $request)
    {
        $payload = $request->validated();

        return DB::transaction(function () use ($payload) {
            $newId = DB::table('tb_mas_tuition_year')->insertGetId($payload);

            return response()->json([
                'success' => true,
                'message' => 'Successfully Added',
                'newid'   => (int) $newId,
            ]);
        });
    }

    /**
     * POST /api/v1/tuition-years/finalize
     * Generic update on tb_mas_tuition_year
     * Body: { intID, ...fields }
     */
    public function finalize(Request $request)
    {
        $id = (int) $request->input('intID', 0);
        if ($id <= 0) {
            return response()->json(['success' => false, 'message' => 'intID required'], 422);
        }
        $data = $request->except(['intID']);
        if (empty($data)) {
            return response()->json(['success' => false, 'message' => 'No fields to update'], 422);
        }

        DB::table('tb_mas_tuition_year')->where('intID', $id)->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Updated'
        ]);
    }

    /**
     * POST /api/v1/tuition-years/submit-extra
     * Adds a row to one of the extra tables:
     * type in {misc, lab_fee, track, program, elective}
     * Body must include the FK column (tuitionYearID or tuitionyear_id depending on table).
     */
    public function submitExtra(Request $request)
    {
        $type = $request->input('type');
        if (!in_array($type, ['misc','lab_fee','track','program','elective','installment'])) {
            return response()->json(['success' => false, 'message' => 'Invalid type'], 422);
        }

        $table = 'tb_mas_tuition_year_' . $type;
        $data  = $request->except(['type']);

        if (in_array($type, ['track','program','elective','installment'])) {
            // expects tuitionyear_id
            if (!$request->has('tuitionyear_id')) {
                return response()->json(['success' => false, 'message' => 'tuitionyear_id required'], 422);
            }
        } else {
            // misc/lab_fee expects tuitionYearID
            if (!$request->has('tuitionYearID')) {
                return response()->json(['success' => false, 'message' => 'tuitionYearID required'], 422);
            }
        }

        DB::table($table)->insert($data);

        return response()->json([
            'success' => true,
            'message' => 'Successfully Added'
        ]);
    }

    /**
     * POST /api/v1/tuition-years/delete-type
     * Deletes an extra row by type and id.
     * Body: { type, id }
     * For track/program/elective PK is 'id', for others PK is 'intID'
     */
    public function deleteType(Request $request)
    {
        $type = $request->input('type');
        $id   = $request->input('id');

        if (!in_array($type, ['misc','lab_fee','track','program','elective','installment'])) {
            return response()->json(['success' => false, 'message' => 'Invalid type'], 422);
        }
        if (!$id) {
            return response()->json(['success' => false, 'message' => 'id required'], 422);
        }

        $table = 'tb_mas_tuition_year_' . $type;
        $pk    = in_array($type, ['track','program','elective','installment']) ? 'id' : 'intID';

        // Prevent deleting an installment plan that is in use by any registration
        if ($type === 'installment') {
            $inUse = DB::table('tb_mas_registration')->where('tuition_installment_plan_id', $id)->count();
            if ($inUse > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete: installment plan is in use by ' . (int)$inUse . ' registration(s).'
                ], 422);
            }
        }

        DB::table($table)->where($pk, $id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Successfully Deleted'
        ]);
    }

    /**
     * POST /api/v1/tuition-years/delete
     * Deletes a tuition year by intID
     * Body: { id }
     */
    public function delete(Request $request)
    {
        $id = (int) $request->input('id', 0);
        if ($id <= 0) {
            return response()->json(['success' => false, 'message' => 'id required'], 422);
        }

        DB::table('tb_mas_tuition_year')->where('intID', $id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'success'
        ]);
    }

    /**
     * POST /api/v1/tuition-years/duplicate
     * Duplicates a tuition year and its related rows.
     * Body: { id }
     */
    public function duplicate(Request $request)
    {
        $id = (int) $request->input('id', 0);
        if ($id <= 0) {
            return response()->json(['success' => false, 'message' => 'id required'], 422);
        }

        return DB::transaction(function () use ($id) {

            $ty = (array) DB::table('tb_mas_tuition_year')->where('intID', $id)->first();
            if (!$ty) {
                return response()->json(['success' => false, 'message' => 'Tuition year not found'], 404);
            }

            unset($ty['intID']);
            // mark duplicated year
            if (isset($ty['year'])) {
                $ty['year'] = $ty['year'] . 'Dup';
            }

            DB::table('tb_mas_tuition_year')->insert($ty);
            $newId = (int) DB::getPdo()->lastInsertId();

            // misc
            $misc = DB::table('tb_mas_tuition_year_misc')->where('tuitionYearID', $id)->get();
            foreach ($misc as $m) {
                $row = (array) $m;
                unset($row['intID']);
                $row['tuitionYearID'] = $newId;
                DB::table('tb_mas_tuition_year_misc')->insert($row);
            }

            // lab fees
            $labs = DB::table('tb_mas_tuition_year_lab_fee')->where('tuitionYearID', $id)->get();
            foreach ($labs as $m) {
                $row = (array) $m;
                unset($row['intID']);
                $row['tuitionYearID'] = $newId;
                DB::table('tb_mas_tuition_year_lab_fee')->insert($row);
            }

            // track
            $tracks = DB::table('tb_mas_tuition_year_track')->where('tuitionyear_id', $id)->get();
            foreach ($tracks as $m) {
                $row = (array) $m;
                unset($row['id']);
                $row['tuitionyear_id'] = $newId;
                DB::table('tb_mas_tuition_year_track')->insert($row);
            }

            // program
            $programs = DB::table('tb_mas_tuition_year_program')->where('tuitionyear_id', $id)->get();
            foreach ($programs as $m) {
                $row = (array) $m;
                unset($row['id']);
                $row['tuitionyear_id'] = $newId;
                DB::table('tb_mas_tuition_year_program')->insert($row);
            }

            // electives
            $electives = DB::table('tb_mas_tuition_year_elective')->where('tuitionyear_id', $id)->get();
            foreach ($electives as $m) {
                $row = (array) $m;
                unset($row['id']);
                $row['tuitionyear_id'] = $newId;
                DB::table('tb_mas_tuition_year_elective')->insert($row);
            }

            return response()->json([
                'success' => true,
                'message' => 'Duplicated',
                'newid'   => $newId
            ]);
        });
    }

    /**
     * POST /api/v1/tuition-years/{id}/set-default?scope=college|shs
     * Resets defaults and sets default for the given id based on scope.
     */
    public function setDefault(Request $request, $id)
    {
        $id = (int) $id;
        if ($id <= 0) {
            return response()->json(['success' => false, 'message' => 'id required'], 422);
        }

        $scope = strtolower((string) $request->query('scope', ''));
        if (!in_array($scope, ['college','shs'])) {
            return response()->json(['success' => false, 'message' => 'Invalid scope'], 422);
        }

        $exists = DB::table('tb_mas_tuition_year')->where('intID', $id)->exists();
        if (!$exists) {
            return response()->json(['success' => false, 'message' => 'Tuition year not found'], 404);
        }

        DB::transaction(function () use ($scope, $id) {
            if ($scope === 'college') {
                DB::table('tb_mas_tuition_year')->update(['isDefault' => 0]);
                DB::table('tb_mas_tuition_year')->where('intID', $id)->update(['isDefault' => 1]);
            } else { // shs
                DB::table('tb_mas_tuition_year')->update(['isDefaultShs' => 0]);
                DB::table('tb_mas_tuition_year')->where('intID', $id)->update(['isDefaultShs' => 1]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Updated'
        ]);
    }

    /**
     * POST /api/v1/tuition-years/edit-type
     * Updates an extra row (misc, lab_fee, track, program, elective)
     * Body: { type, id, ...fields }
     * For track/program/elective PK is 'id', for others PK is 'intID'
     */
    public function editType(Request $request)
    {
        // Use 'xtype' (preferred) to denote which table to update, fallback to 'type' for backward-compat
        $tableType = $request->input('xtype', $request->input('type'));
        $id        = $request->input('id');

        if (!in_array($tableType, ['misc','lab_fee','track','program','elective','installment'])) {
            return response()->json(['success' => false, 'message' => 'Invalid type'], 422);
        }
        if (!$id) {
            return response()->json(['success' => false, 'message' => 'id required'], 422);
        }

        $table = 'tb_mas_tuition_year_' . $tableType;
        $pk    = in_array($tableType, ['track','program','elective','installment']) ? 'id' : 'intID';

        // Determine allowed fields per type (prevent FK/PK tampering)
        $allowed = [];
        if ($tableType === 'misc') {
            // For misc rows, allow updating the row's 'type' column (category) safely
            $allowed = ['name','miscRegular','miscOnline','miscHyflex','miscHybrid','type'];
        } elseif ($tableType === 'lab_fee') {
            $allowed = ['name','labRegular','labOnline','labHyflex','labHybrid'];
        } elseif (in_array($tableType, ['track','program'])) {
            $allowed = ['track_id','tuition_amount','tuition_amount_online','tuition_amount_hyflex','tuition_amount_hybrid'];
        } elseif ($tableType === 'elective') {
            $allowed = ['subject_id','tuition_amount','tuition_amount_online','tuition_amount_hyflex','tuition_amount_hybrid'];
        } elseif ($tableType === 'installment') {
            $allowed = ['code','label','dp_type','dp_value','increase_percent','installment_count','is_active','sort_order','level'];
        }

        $payload = $request->only($allowed);
        if (empty($payload)) {
            return response()->json(['success' => false, 'message' => 'No fields to update'], 422);
        }

        $exists = DB::table($table)->where($pk, $id)->exists();
        if (!$exists) {
            return response()->json(['success' => false, 'message' => 'Record not found'], 404);
        }

        DB::table($table)->where($pk, $id)->update($payload);

        return response()->json([
            'success' => true,
            'message' => 'Successfully Updated'
        ]);
    }
}
