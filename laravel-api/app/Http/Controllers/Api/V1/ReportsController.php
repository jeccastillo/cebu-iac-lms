<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Exports\EnrolledStudentsExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
    /**
     * GET /api/v1/reports/enrolled-students/export?syid=INT
     *
     * Validates the selected term (syid) and returns an Excel file containing
     * enrolled students for that term. Enrollment filter: r.intAYID = :syid AND r.intROG = 1.
     * Program code is derived via COALESCE(r.current_program, u.intProgramID).
     *
     * Guarded by middleware in routes: role:registrar,admin
     */
    public function enrolledStudentsExport(Request $request)
    {
        $payload = $request->validate([
            'syid' => 'required|integer',
        ]);

        $syid = (int) $payload['syid'];
        $filename = 'enrolled-students-' . now()->format('Ymd-His') . '.xlsx';

        return Excel::download(new EnrolledStudentsExport($syid), $filename);
    }

    public function dailyEnrollmentSummary(Request $request)
    {
        $payload = $request->validate([
            'syid' => 'required|integer',
            'date_from' => 'nullable|date_format:Y-m-d',
            'date_to' => 'nullable|date_format:Y-m-d|after_or_equal:date_from',
        ]);

        $syid = (int) $payload['syid'];
        $dateFrom = $payload['date_from'] ?? null;
        $dateTo = $payload['date_to'] ?? null;

        $dataBuckets = [];

        $totals = [
            'freshman' => 0,
            'transferee' => 0,
            'second' => 0,
            'continuing' => 0,
            'shiftee' => 0,
            'returning' => 0,
            'total' => 0,
        ];

        // Build base query
        $query = DB::table('tb_mas_registration')
            ->select(DB::raw('DATE(dteRegistered) as d'), 'enumStudentType')
            ->where('intAYID', $syid)
            ->where('intROG', 1);

        // Apply optional date filters
        if (!empty($dateFrom)) {
            $query->where('dteRegistered', '>=', $dateFrom . ' 00:00:00');
        }
        if (!empty($dateTo)) {
            // Inclusive end date via exclusive upper-bound at next day 00:00:00
            $endExclusive = date('Y-m-d', strtotime($dateTo . ' +1 day'));
            $query->where('dteRegistered', '<', $endExclusive . ' 00:00:00');
        }

        $rows = $query->orderBy('d')->get();

        $mapType = function (?string $t): ?string {
            if ($t === null) return null;
            $x = strtolower(trim($t));
            if ($x === 'freshman' || $x === 'new') return 'freshman';
            if ($x === 'transferee') return 'transferee';
            if ($x === 'second degree' || $x === 'second') return 'second';
            if ($x === 'continuing' || $x === 'old') return 'continuing';
            if ($x === 'shiftee') return 'shiftee';
            if ($x === 'returning' || $x === 'returnee') return 'returning';
            // ignore unknowns
            return null;
        };

        if (!empty($dateFrom) && !empty($dateTo)) {
            // Pre-initialize buckets for provided range
            $endExclusive = date('Y-m-d', strtotime($dateTo . ' +1 day'));
            $period = new \DatePeriod(new \DateTime($dateFrom), new \DateInterval('P1D'), new \DateTime($endExclusive));
            foreach ($period as $dt) {
                $d = $dt->format('Y-m-d');
                $dataBuckets[$d] = [
                    'date' => date('M j, Y', strtotime($d)),
                    'freshman' => 0,
                    'transferee' => 0,
                    'second' => 0,
                    'continuing' => 0,
                    'shiftee' => 0,
                    'returning' => 0,
                    'total' => 0,
                ];
            }
        }

        foreach ($rows as $row) {
            $dateKey = $row->d;

            // If no pre-initialized bucket (no range provided), create on the fly
            if (!isset($dataBuckets[$dateKey])) {
                $dataBuckets[$dateKey] = [
                    'date' => date('M j, Y', strtotime($dateKey)),
                    'freshman' => 0,
                    'transferee' => 0,
                    'second' => 0,
                    'continuing' => 0,
                    'shiftee' => 0,
                    'returning' => 0,
                    'total' => 0,
                ];
            }

            $bucketKey = $mapType($row->enumStudentType);
            if ($bucketKey === null) {
                continue;
            }
            $dataBuckets[$dateKey][$bucketKey] += 1;
            $dataBuckets[$dateKey]['total'] += 1;

            $totals[$bucketKey] += 1;
            $totals['total'] += 1;
        }

        // Reindex to sequential array (preserving order as iterated)
        $data = array_values($dataBuckets);

        return response()->json([
            'syid' => $syid,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'data' => $data,
            'totals' => $totals,
        ]);
    }
}
