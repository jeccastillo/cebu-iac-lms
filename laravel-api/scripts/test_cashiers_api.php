<?php
/**
 * Internal-kernel smoke tests for Cashier Admin endpoints (no external curl required).
 *
 * Usage:
 *   php laravel-api/scripts/test_cashiers_api.php [faculty_id]
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

$facultyId = isset($argv[1]) ? (int)$argv[1] : 13;

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

echo "=== Cashier Admin API Internal Smoke (faculty_id={$facultyId}) ===\n\n";

$headers = [
    'X-Faculty-ID' => (string)$facultyId,
    'Accept'       => 'application/json',
];

// 1) GET /cashiers?includeStats=1
$res1 = call_api($kernel, 'GET', "/api/v1/cashiers?includeStats=1", $headers);

// Try to parse list and pick first id
$firstId = null;
$decoded1 = json_decode($res1['body'], true);
if (json_last_error() === JSON_ERROR_NONE && isset($decoded1['data']) && is_array($decoded1['data']) && count($decoded1['data']) > 0) {
    $first = $decoded1['data'][0];
    if (isset($first['id'])) {
        $firstId = (int)$first['id'];
    }
}

// 2) GET /cashiers/stats
call_api($kernel, 'GET', "/api/v1/cashiers/stats?page=1&amp;perPage=5", $headers);

// 3) If we have a cashier id, GET /cashiers/{id}/stats
if ($firstId) {
    call_api($kernel, 'GET', "/api/v1/cashiers/{$firstId}/stats", $headers);
} else {
    echo "No cashier row found in list; per-id stats skipped.\n\n";
}

echo "=== Done ===\n";

// Properly terminate the kernel (optional for CLI)
$kernel->terminate(Request::capture(), response());
