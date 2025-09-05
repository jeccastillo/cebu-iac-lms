<?php
/**
 * Migration script:
 * 1) Read all rows from tb_mas_sy2 (legacy terms).
 * 2) For each row, ensure a matching tb_mas_sy row exists (new intID). Match key: (strYearStart, strYearEnd, enumSem, campus_id).
 *    - If not present, insert to tb_mas_sy and capture new intID.
 *    - If present, reuse existing tb_mas_sy.intID.
 * 3) Update tb_mas_applicant_data.syid for applicants whose user campus_id = 1 (or --campus-filter), mapping old sy2 ids to the new tb_mas_sy ids using the key match above.
 *
 * Usage:
 *   php laravel-api/scripts/migrate_sy2_to_sy_and_update_applicants.php [--dry-run] [--source=tb_mas_sy2] [--campus-default=1] [--campus-filter=1]
 *
 * Options:
 *   --dry-run           Print actions without writing changes
 *   --source            Source table name for legacy terms (default: tb_mas_sy2)
 *   --campus-default    Value to use for campus_id if tb_mas_sy2 lacks that column (default: 1)
 *   --campus-filter     Only update applicants where tb_mas_users.campus_id = this value (default: 1)
 */

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

function out($k, $v) {
    if (!is_scalar($v)) {
        $v = json_encode($v, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
    }
    echo $k . '=' . $v . PHP_EOL;
}

function argv_has(string $name): bool {
    global $argv;
    foreach ($argv as $a) {
        if ($a === $name) return true;
    }
    return false;
}

function argv_get(string $prefix, $default = null) {
    global $argv;
    foreach ($argv as $a) {
        if (strpos($a, $prefix . '=') === 0) {
            return substr($a, strlen($prefix) + 1);
        }
    }
    return $default;
}

$dryRun         = argv_has('--dry-run');
$sourceTable    = (string) argv_get('--source', 'tb_mas_sy2');
$campusDefault  = (int) argv_get('--campus-default', 1);
$campusFilter   = (int) argv_get('--campus-filter', 1);

out('dry_run', $dryRun ? '1' : '0');
out('source_table', $sourceTable);
out('campus_default', $campusDefault);
out('campus_filter', $campusFilter);

// Validate required tables
$requiredTables = [
    $sourceTable,
    'tb_mas_sy',
    'tb_mas_users',
    'tb_mas_applicant_data',
];
$missing = [];
foreach ($requiredTables as $t) {
    if (!Schema::hasTable($t)) $missing[] = $t;
}
if (!empty($missing)) {
    out('error', 'missing_tables: ' . implode(',', $missing));
    exit(1);
}

// Columns
$sy2Cols = Schema::getColumnListing($sourceTable);
$syCols  = Schema::getColumnListing('tb_mas_sy');
$sy2HasCampus = in_array('campus_id', $sy2Cols, true);
$syHasCampus  = in_array('campus_id', $syCols, true);

// Intersection for copy (exclude primary key)
$copyCols = array_values(array_diff(array_intersect($sy2Cols, $syCols), ['intID']));
// Ensure campus_id present in $copyCols if target has it (we may need to synthesize a value)
if ($syHasCampus && !in_array('campus_id', $copyCols, true)) {
    $copyCols[] = 'campus_id';
}

out('sy2_columns', $sy2Cols);
out('sy_columns', $syCols);
out('copy_columns', $copyCols);
out('sy2_has_campus', $sy2HasCampus ? 1 : 0);
out('sy_has_campus', $syHasCampus ? 1 : 0);

// Utility: sanitize date/datetime values (convert zero dates to null)
$zeroDates = ['0000-00-00 00:00:00', '0000-00-00', '0000-00-00 00:00'];
$sanitize = function($col, $val) use ($zeroDates) {
    if ($val === null) return null;
    if (is_string($val) && in_array($val, $zeroDates, true)) {
        return null;
    }
    return $val;
};

// Key resolver
$getKey = function($row) use ($sy2HasCampus, $campusDefault) {
    $key = [
        'strYearStart' => property_exists($row, 'strYearStart') ? (string) $row->strYearStart : null,
        'strYearEnd'   => property_exists($row, 'strYearEnd') ? (string) $row->strYearEnd : null,
        'enumSem'      => property_exists($row, 'enumSem') ? (string) $row->enumSem : null,
        'term_student_type' => property_exists($row, 'term_student_type') ? (string) $row->term_student_type : null,
        'campus_id'    => $sy2HasCampus ? (int) ($row->campus_id ?? 0) : (int) $campusDefault,
    ];
    return $key;
};

// Stats
$created = 0;
$matched = 0;

// Phase 1: Ensure tb_mas_sy rows exist for each tb_mas_sy2 row
out('phase', 'copy_sy2_to_sy_start');

try {
    // Wrap creation phase in a transaction to ensure atomicity when not in dry-run
    if (!$dryRun) {
        DB::beginTransaction();
    }

    // Iterate sy2 in chunks for memory safety
    DB::table($sourceTable)
        ->orderBy('intID', 'asc')
        ->chunk(500, function($rows) use (&$created, &$matched, $getKey, $copyCols, $sanitize, $dryRun, $syHasCampus, $sy2HasCampus, $campusDefault) {
            foreach ($rows as $row) {
                $key = $getKey($row);

                // Validate required key parts
                if ($key['strYearStart'] === null || $key['strYearEnd'] === null || $key['enumSem'] === null) {
                    out('warn_missing_key', ['sy2_id' => $row->intID ?? null, 'key' => $key]);
                    continue;
                }

                // Try find existing in tb_mas_sy using key
                $q = DB::table('tb_mas_sy')
                    ->where('strYearStart', $key['strYearStart'])
                    ->where('strYearEnd', $key['strYearEnd'])
                    ->where('enumSem', $key['enumSem'])
                    ->where('term_student_type', $key['term_student_type']);                    


                    

                if ($syHasCampus) {
                    $q->where(function($sub) use ($key) {
                        // Exact match; allow null=null if needed
                        if ($key['campus_id'] === null) {
                            $sub->whereNull('campus_id');
                        } else {
                            $sub->where('campus_id', $key['campus_id']);
                        }
                    });
                }

                $existing = $q->first();

                if ($existing) {   
                                                         
                    $matched++;
                    continue;
                }

                // Prepare insert data
                $insert = [];
                foreach ($copyCols as $c) {                    
                    if ($c === 'campus_id') {
                        // Derive campus_id
                        $val = $sy2HasCampus ? ($row->campus_id ?? null) : $campusDefault;
                        $insert[$c] = $val !== null ? (int) $val : null;
                        continue;
                    }
                    if (!property_exists($row, $c)) {
                        // column exists in target but not in source; set null
                        $insert[$c] = null;
                        continue;
                    }
                    $insert[$c] = $sanitize($c, $row->{$c});
                }

                if ($dryRun) {
                    $created++;
                    out('would_insert_sy', ['from_sy2_id' => $row->intID ?? null, 'key' => $key, 'data' => $insert]);
                } else {
                    try {
                        // Respect unique index if present (strYearStart,strYearEnd,enumSem,campus_id)
                        $newId = DB::table('tb_mas_sy')->insertGetId($insert);                        
                        $created++;
                        out('inserted_sy', ['new_intID' => $newId, 'from_sy2_id' => $row->intID ?? null, 'key' => $key]);
                    } catch (\Throwable $e) {
                        // If duplicate or any other error, log and continue
                        out('insert_sy_error', ['from_sy2_id' => $row->intID ?? null, 'key' => $key, 'error' => $e->getMessage()]);
                    }
                }
            }
        });

    if (!$dryRun) {
        DB::commit();
    }
} catch (\Throwable $e) {
    if (!$dryRun) {
        DB::rollBack();
    }
    out('error_phase1', $e->getMessage());
    exit(1);
}

out('phase', 'copy_sy2_to_sy_done');
out('sy_rows_created', $created);
out('sy_rows_matched_existing', $matched);

// Phase 2: Update tb_mas_applicant_data.syid for users in campus_id filter, mapping via key match
out('phase', 'update_applicants_syid_start');

try {
    if ($dryRun) {
        // Compute how many would update (count only)
        if ($sy2HasCampus) {
            $count = DB::table('tb_mas_applicant_data as ad')
                ->join('tb_mas_users as u', 'u.intID', '=', 'ad.user_id')
                ->join($sourceTable . ' as sy2', 'sy2.intID', '=', 'ad.syid')
                ->join('tb_mas_sy as sy', function($join) {
                    $join->on('sy.strYearStart', '=', 'sy2.strYearStart')
                         ->on('sy.strYearEnd',   '=', 'sy2.strYearEnd')
                         ->on('sy.enumSem',      '=', 'sy2.enumSem')
                         ->on('sy.campus_id',    '=', 'sy2.campus_id');
                })
                ->where('u.campus_id', '=', $campusFilter)
                ->whereColumn('ad.syid', '<>', 'sy.intID')
                ->count();
        } else {
            // sy2 has no campus_id: match with campus_default
            $count = DB::table('tb_mas_applicant_data as ad')
                ->join('tb_mas_users as u', 'u.intID', '=', 'ad.user_id')
                ->join($sourceTable . ' as sy2', 'sy2.intID', '=', 'ad.syid')
                ->join('tb_mas_sy as sy', function($join) use ($campusDefault) {
                    $join->on('sy.strYearStart', '=', 'sy2.strYearStart')
                         ->on('sy.strYearEnd',   '=', 'sy2.strYearEnd')
                         ->on('sy.enumSem',      '=', 'sy2.enumSem')
                         ->where('sy.campus_id', '=', $campusDefault);
                })
                ->where('u.campus_id', '=', $campusFilter)
                ->whereColumn('ad.syid', '<>', 'sy.intID')
                ->count();
        }
        out('would_update_applicants', $count);
    } else {
        // Execute UPDATE with JOIN
        if ($sy2HasCampus) {
            $sql = <<<SQL
UPDATE tb_mas_applicant_data ad
JOIN tb_mas_users u ON u.intID = ad.user_id
JOIN {$sourceTable} sy2 ON sy2.intID = ad.syid
JOIN tb_mas_sy sy
  ON sy.strYearStart = sy2.strYearStart
 AND sy.strYearEnd   = sy2.strYearEnd
 AND sy.enumSem      = sy2.enumSem
 AND sy.campus_id    = sy2.campus_id
SET ad.syid = sy.intID
WHERE u.campus_id = ?
  AND ad.syid IS NOT NULL
  AND ad.syid <> sy.intID
SQL;
            $affected = DB::update($sql, [$campusFilter]);
        } else {
            $sql = <<<SQL
UPDATE tb_mas_applicant_data ad
JOIN tb_mas_users u ON u.intID = ad.user_id
JOIN {$sourceTable} sy2 ON sy2.intID = ad.syid
JOIN tb_mas_sy sy
  ON sy.strYearStart = sy2.strYearStart
 AND sy.strYearEnd   = sy2.strYearEnd
 AND sy.enumSem      = sy2.enumSem
 AND sy.campus_id    = ?
SET ad.syid = sy.intID
WHERE u.campus_id = ?
  AND ad.syid IS NOT NULL
  AND ad.syid <> sy.intID
SQL;
            $affected = DB::update($sql, [$campusDefault, $campusFilter]);
        }
        out('applicants_updated', $affected);
    }
} catch (\Throwable $e) {
    out('error_phase2', $e->getMessage());
    exit(1);
}

out('phase', 'update_applicants_syid_done');
out('status', 'ok');
?>
