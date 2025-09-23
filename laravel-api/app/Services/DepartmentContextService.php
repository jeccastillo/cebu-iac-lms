<?php

namespace App\Services;

use App\Models\Cashier;
use Illuminate\Http\Request;

class DepartmentContextService
{
    /**
     * Resolve campus_id using precedence:
     *  - Explicit query/input campus_id (numeric)
     *  - X-Campus-ID header
     *  - Cashier campus via X-Faculty-ID header
     */
    public function resolveCampusId(Request $request): ?int
    {
        $resolvedCampusId = null;

        $reqCampus = $request->input('campus_id', $request->query('campus_id'));
        if ($reqCampus !== null && $reqCampus !== '' && is_numeric($reqCampus)) {
            $resolvedCampusId = (int) $reqCampus;
        } else {
            $hdrCampus = $request->header('X-Campus-ID');
            if ($hdrCampus !== null && $hdrCampus !== '' && is_numeric($hdrCampus)) {
                $resolvedCampusId = (int) $hdrCampus;
            } else {
                $hdrFaculty = $request->header('X-Faculty-ID');
                if ($hdrFaculty !== null && $hdrFaculty !== '' && is_numeric($hdrFaculty)) {
                    $cashier = Cashier::query()->where('faculty_id', (int) $hdrFaculty)->select('campus_id')->first();
                    if ($cashier && isset($cashier->campus_id)) {
                        $resolvedCampusId = (int) $cashier->campus_id;
                    }
                }
            }
        }

        return $resolvedCampusId;
    }

    /**
     * Canonical list of department codes.
     *
     * @return array<int,string>
     */
    public function departmentCodes(): array
    {
        $codes = config('departments.codes', []);
        if (!is_array($codes) || empty($codes)) {
            $codes = [
                'registrar',
                'finance',
                'admissions',
                'building_admin',
                'purchasing',
                'academics',
                'clinic',
                'guidance',
                'osas',
                'soc',
                'soda',
                'sobla',
            ];
        }
        return array_values(array_unique(array_map(function ($c) {
            return strtolower((string) $c);
        }, $codes)));
    }
}
