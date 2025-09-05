<?php
/**
 * Simple API smoke test for Cashier Admin endpoints.
 *
 * Usage:
 *   php laravel-api/scripts/smoke_cashiers.php [faculty_id]
 *
 * Defaults:
 *   faculty_id = 13 (must have cashier_admin or admin role)
 */

$base = 'http://localhost/iacademy/cebu-iac-lms/laravel-api/public/api/v1';
$facultyId = isset($argv[1]) ? (int)$argv[1] : 13;

function http_request(string $method, string $url, array $headers = [], ?string $body = null): array
{
    $ch = curl_init();
    $hdrs = array_merge([
        'Accept: application/json',
    ], $headers);

    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_CUSTOMREQUEST  => strtoupper($method),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_HEADER         => true, // include headers in output
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_HTTPHEADER     => $hdrs,
    ]);

    if ($body !== null) {
        $hdrs[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $hdrs);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    }

    $resp = curl_exec($ch);
    if ($resp === false) {
        $err = curl_error($ch);
        curl_close($ch);
        return [
            'status' => 0,
            'headers' => '',
            'body' => '',
            'error' => $err,
        ];
    }

    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $rawHeaders = substr($resp, 0, $headerSize);
    $rawBody    = substr($resp, $headerSize);

    curl_close($ch);
    return [
        'status'  => (int)$statusCode,
        'headers' => $rawHeaders,
        'body'    => $rawBody,
        'error'   => null,
    ];
}

function print_section(string $title)
{
    echo PHP_EOL, str_repeat('=', 12), ' ', $title, ' ', str_repeat('=', 12), PHP_EOL;
}

function print_result(string $label, array $res, int $truncate = 400)
{
    echo PHP_EOL, '--- ', $label, ' ---', PHP_EOL;
    echo 'HTTP ', $res['status'], PHP_EOL;
    if (!empty($res['error'])) {
        echo 'Error: ', $res['error'], PHP_EOL;
        return;
    }
    // Try to pretty-print JSON
    $body = $res['body'];
    $decoded = json_decode($body, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        $pretty = json_encode($decoded, JSON_PRETTY_PRINT);
        if ($truncate > 0 && strlen($pretty) > $truncate) {
            $pretty = substr($pretty, 0, $truncate) . "...(truncated)";
        }
        echo $pretty, PHP_EOL;
    } else {
        if ($truncate > 0 && strlen($body) > $truncate) {
            $body = substr($body, 0, $truncate) . "...(truncated)";
        }
        echo $body, PHP_EOL;
    }
}

print_section('Cashier Admin API Smoke (faculty_id=' . $facultyId . ')');

// 1) GET /cashiers (no header) — expect 401/403
$url1 = $base . '/cashiers?includeStats=1';
$res1 = http_request('GET', $url1);
print_result('GET /cashiers (no header, expect 401/403)', $res1);

// 2) GET /cashiers with X-Faculty-ID — expect 200 and data array
$headersAuth = ['X-Faculty-ID: ' . $facultyId];
$res2 = http_request('GET', $url1, $headersAuth);
print_result('GET /cashiers (with X-Faculty-ID, expect 200)', $res2);

// Parse list to get first cashier id if present
$firstId = null;
$decoded2 = json_decode($res2['body'], true);
if (json_last_error() === JSON_ERROR_NONE && isset($decoded2['data']) && is_array($decoded2['data']) && count($decoded2['data']) > 0) {
    $firstRow = $decoded2['data'][0];
    if (isset($firstRow['id'])) {
        $firstId = (int)$firstRow['id'];
    }
}

// 3) GET /cashiers/stats (with header)
$url3 = $base . '/cashiers/stats?page=1&perPage=5';
$res3 = http_request('GET', $url3, $headersAuth);
print_result('GET /cashiers/stats (with X-Faculty-ID, expect 200)', $res3);

// 4) If we found a cashier id, GET its stats
if ($firstId) {
    $url4 = $base . '/cashiers/' . $firstId . '/stats';
    $res4 = http_request('GET', $url4, $headersAuth);
    print_result("GET /cashiers/{$firstId}/stats (with X-Faculty-ID, expect 200)", $res4);
} else {
    echo PHP_EOL, 'No cashier id found from list; skipping per-id stats test.', PHP_EOL;
}

echo PHP_EOL, 'Smoke tests complete.', PHP_EOL;
