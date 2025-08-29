<?php
/**
 * Thorough tests for Cashier Admin CRUD and validations using the internal Laravel kernel.
 *
 * Usage:
 *   php laravel-api/scripts/test_cashiers_thorough.php [faculty_id]
 * Default faculty_id: 13
 */

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Resolve laravel base (this script resides in laravel-api/scripts/)
$basePath = realpath(__DIR__ . '/..');
if ($basePath === false) {
    fwrite(STDERR, "Failed to resolve laravel base path\n");
    exit(1);
}

require $basePath . '/vendor/autoload.php';
$app = require_once $basePath . '/bootstrap/app.php';

/** @var Kernel $kernel */
$kernel = $app->make(Kernel::class);

$actingFacultyId = isset($argv[1]) ? (int)$argv[1] : 13;

function call_api(Kernel $kernel, string $method, string $uri, array $headers = [], array $payload = []): array {
    $request = Request::create($uri, $method, $payload);
    foreach ($headers as $k => $v) {
        $request->headers->set($k, $v);
    }

    $response = $kernel->handle($request);
    $status   = $response->getStatusCode();
    $content  = $response->getContent();

    echo "=== $method $uri ===\n";
    echo "Status: $status\n";
    echo "Body:\n";
    echo $content . "\n\n";

    return ['status' => $status, 'body' => $content];
}

function json_data(array $res) {
    $data = json_decode($res['body'], true);
    return (json_last_error() === JSON_ERROR_NONE) ? $data : null;
}

echo "=== Cashier Admin API THOROUGH TESTS (acting faculty_id={$actingFacultyId}) ===\n\n";

$headers = [
    'X-Faculty-ID' => (string)$actingFacultyId,
    'Accept'       => 'application/json',
];

// Helper to fetch current list
function list_cashiers(Kernel $kernel, array $headers, bool $includeStats = false, ?int $campusId = null): array {
    $qs = [];
    if ($includeStats) $qs[] = 'includeStats=1';
    if (!is_null($campusId)) $qs[] = 'campus_id=' . urlencode((string)$campusId);
    $uri = '/api/v1/cashiers' . (count($qs) ? ('?' . implode('&', $qs)) : '');
    $res = call_api($kernel, 'GET', $uri, $headers);
    $jd  = json_data($res);
    return ($jd && isset($jd['data']) && is_array($jd['data'])) ? $jd['data'] : [];
}

// 0) Pre-flight: get list; require at least one row to anchor tests
$list = list_cashiers($kernel, $headers, false, null);
if (count($list) === 0) {
    echo "No cashiers found; cannot proceed with thorough tests safely.\n";
    echo "Please seed a cashier row first or run scripts/test_cashiers_api.php to verify.\n";
    exit(1);
}

$first = $list[0];
$firstId = (int)($first['id'] ?? 0);
$firstCampusId = isset($first['campus_id']) ? (int)$first['campus_id'] : null;
$firstFacultyId = isset($first['faculty_id']) && !is_null($first['faculty_id']) ? (int)$first['faculty_id'] : null;
$firstOrStart = $first['or']['start'] ?? null;
$firstOrEnd   = $first['or']['end'] ?? null;

echo "Anchor cashier: id={$firstId}, campus_id=" . var_export($firstCampusId, true) . ", faculty_id=" . var_export($firstFacultyId, true) . "\n\n";

// 1) SHOW endpoint tests
echo "--- 1) SHOW endpoint tests ---\n";
call_api($kernel, 'GET', "/api/v1/cashiers/{$firstId}", $headers);
call_api($kernel, 'GET', "/api/v1/cashiers/{$firstId}?includeStats=1", $headers);
call_api($kernel, 'GET', "/api/v1/cashiers/999999", $headers); // expect 404

// 2) Create a temporary cashier in same campus with non-overlapping ranges
echo "--- 2) CREATE temporary cashier ---\n";

// Gather used faculty_ids in campus
$usedFacultyIds = [];
foreach ($list as $row) {
    if (isset($row['campus_id']) && (int)$row['campus_id'] === (int)$firstCampusId) {
        if (isset($row['faculty_id']) && !is_null($row['faculty_id'])) {
            $usedFacultyIds[(int)$row['faculty_id']] = true;
        }
    }
}

// Search for a free faculty in the same campus
$searchUri = "/api/v1/faculty/search?campus_id=" . urlencode((string)$firstCampusId) . "&per_page=50";
$resSearch = call_api($kernel, 'GET', $searchUri, $headers);
$searchData = json_data($resSearch);
$candidateFacultyId = null;
if ($searchData && isset($searchData['data']) && is_array($searchData['data'])) {
    foreach ($searchData['data'] as $f) {
        $fid = isset($f['intID']) ? (int)$f['intID'] : (isset($f['id']) ? (int)$f['id'] : null);
        if ($fid && empty($usedFacultyIds[$fid])) {
            $candidateFacultyId = $fid;
            break;
        }
    }
}

if (!$candidateFacultyId) {
    echo "No free faculty candidate found in campus {$firstCampusId}; will attempt using the first list item faculty (expect duplicate validation later).\n";
    $candidateFacultyId = $firstFacultyId ?: 0;
}

$createPayload = [
    'faculty_id'     => $candidateFacultyId,
    'campus_id'      => $firstCampusId,
    'or_start'       => 990000,
    'or_end'         => 990010,
    'invoice_start'  => 991000,
    'invoice_end'    => 991010,
    'temporary_admin'=> 0,
];
$resCreate = call_api($kernel, 'POST', '/api/v1/cashiers', $headers, $createPayload);
$created = json_data($resCreate);
$secondId = null;
if ($created && isset($created['data']['id'])) {
    $secondId = (int)$created['data']['id'];
    echo "Temporary cashier created: id={$secondId}\n\n";
} else {
    echo "Creation failed or duplicate; continuing tests where possible.\n\n";
}

// 3) Duplicate create should 422 (if second created, try again with same payload)
echo "--- 3) DUPLICATE create should 422 ---\n";
call_api($kernel, 'POST', '/api/v1/cashiers', $headers, $createPayload);

// 4) updateRanges overlap validation (use second to overlap first's OR if both exist)
echo "--- 4) updateRanges overlap validation ---\n";
if ($secondId && $firstOrStart && $firstOrEnd) {
    $overlapPayload = [
        'or_start' => $firstOrStart,
        'or_end'   => $firstOrEnd,
    ];
    call_api($kernel, 'POST', "/api/v1/cashiers/{$secondId}/ranges", $headers, $overlapPayload); // expect 422 if overlaps
} else {
    echo "Skipped overlap test (missing secondId or first OR range data).\n\n";
}

// 5) update bounds enforcement for current pointers
echo "--- 5) update current bounds enforcement ---\n";
if ($firstOrEnd) {
    $badUpdate = [
        'or_current' => ((int)$firstOrEnd) + 1,
    ];
    call_api($kernel, 'PATCH', "/api/v1/cashiers/{$firstId}", $headers, $badUpdate); // expect 422
} else {
    echo "Skipped bounds test (first OR range missing).\n\n";
}

// 6) Assignment tests (duplicate and campus mismatch, if possible)
echo "--- 6) Assignment tests ---\n";
if ($secondId) {
    // Duplicate assign: attempt to assign first's faculty to second (same campus)
    if ($firstFacultyId) {
        call_api($kernel, 'PATCH', "/api/v1/cashiers/{$secondId}/assign", $headers, ['faculty_id' => $firstFacultyId]); // expect 422
    } else {
        echo "Skipped duplicate assignment test (first cashier has no faculty_id).\n\n";
    }

    // Campus mismatch attempt: find faculty from another campus (try simple campus_id+1)
    $otherCampusId = $firstCampusId !== null ? ((int)$firstCampusId + 1) : null;
    if ($otherCampusId !== null) {
        $searchOther = call_api($kernel, 'GET', "/api/v1/faculty/search?campus_id={$otherCampusId}&per_page=10", $headers);
        $dataOther = json_data($searchOther);
        $otherFacultyId = null;
        if ($dataOther && isset($dataOther['data']) && is_array($dataOther['data']) && count($dataOther['data']) > 0) {
            $fo = $dataOther['data'][0];
            $otherFacultyId = isset($fo['intID']) ? (int)$fo['intID'] : (isset($fo['id']) ? (int)$fo['id'] : null);
        }
        if ($otherFacultyId) {
            call_api($kernel, 'PATCH', "/api/v1/cashiers/{$secondId}/assign", $headers, ['faculty_id' => $otherFacultyId]); // expect 422
        } else {
            echo "Skipped campus mismatch assignment test (no faculty found in other campus {$otherCampusId}).\n\n";
        }
    } else {
        echo "Skipped campus mismatch (no campus id).\n\n";
    }
} else {
    echo "Skipped assignment tests (no temporary cashier created).\n\n";
}

// 7) SHOW then DESTROY temporary cashier
echo "--- 7) SHOW and DESTROY temporary cashier ---\n";
if ($secondId) {
    call_api($kernel, 'GET', "/api/v1/cashiers/{$secondId}", $headers);
    call_api($kernel, 'DELETE', "/api/v1/cashiers/{$secondId}", $headers); // expect success
    // Verify it's gone
    call_api($kernel, 'GET', "/api/v1/cashiers/{$secondId}", $headers); // expect 404
} else {
    echo "Skipped destroy test (no temporary cashier created).\n\n";
}

echo "=== THOROUGH TESTS COMPLETE ===\n";
