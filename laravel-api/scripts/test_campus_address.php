<?php
// Bootstrap Laravel (CLI)
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\V1\CampusController;

function out($k, $v) {
    if (!is_scalar($v)) {
        $v = json_encode($v);
    }
    echo $k . '=' . $v . PHP_EOL;
}

$controller = new CampusController();

// Track created ID for cleanup
$createdId = null;

// 1) Create campus with address (happy path)
$name = 'ADDR Campus ' . date('His');
$address1 = '123 Test Street, Sample City';
$reqCreate = Request::create('/api/v1/campuses', 'POST', [
    'campus_name' => $name,
    'description' => 'address create test',
    'address'     => $address1,
    'status'      => 'active',
]);
$resCreate = $controller->store($reqCreate);
$statusCreate = method_exists($resCreate, 'getStatusCode') ? $resCreate->getStatusCode() : 0;
$payloadCreate = method_exists($resCreate, 'getData') ? $resCreate->getData(true) : [];
$createdId = $payloadCreate['data']['id'] ?? null;

out('create_status', $statusCreate);
out('created_id', $createdId);
out('create_address', $payloadCreate['data']['address'] ?? null);

// 2) Show campus and verify address present
if ($createdId) {
    $resShow = $controller->show((int)$createdId);
    $statusShow = $resShow->getStatusCode();
    $payloadShow = $resShow->getData(true);
    out('show_status', $statusShow);
    out('show_address', $payloadShow['data']['address'] ?? null);
}

// 3) Update campus address (change value)
if ($createdId) {
    $address2 = '456 Updated Avenue, New City';
    $reqUpdate1 = Request::create('/api/v1/campuses/'.$createdId, 'PUT', [
        'address' => $address2,
    ]);
    $resUpdate1 = $controller->update($reqUpdate1, (int)$createdId);
    $statusUpdate1 = $resUpdate1->getStatusCode();
    $payloadUpdate1 = $resUpdate1->getData(true);
    out('update1_status', $statusUpdate1);
    out('update1_address', $payloadUpdate1['data']['address'] ?? null);
}

// 4) Clear address (set to null)
if ($createdId) {
    $reqUpdate2 = Request::create('/api/v1/campuses/'.$createdId, 'PUT', [
        'address' => null,
    ]);
    $resUpdate2 = $controller->update($reqUpdate2, (int)$createdId);
    $statusUpdate2 = $resUpdate2->getStatusCode();
    $payloadUpdate2 = $resUpdate2->getData(true);
    out('update2_status', $statusUpdate2);
    $addr2 = array_key_exists('address', $payloadUpdate2['data']) ? $payloadUpdate2['data']['address'] : '__missing__';
    out('update2_address', $addr2);
}

// 5) Validation: address too long (>255) on create should 422
$tooLong = str_repeat('A', 256);
try {
    $reqBad = Request::create('/api/v1/campuses', 'POST', [
        'campus_name' => $name . ' X',
        'description' => 'address too long',
        'address'     => $tooLong,
        'status'      => 'active',
    ]);
    $controller->store($reqBad);
    out('create_toolong_status', 'unexpected_success');
} catch (\Illuminate\Validation\ValidationException $ex) {
    out('create_toolong_status', 422);
    out('create_toolong_first_error', reset($ex->errors()) ? reset($ex->errors())[0] : 'validation error');
} catch (\Throwable $t) {
    out('create_toolong_status', 'unexpected_error: ' . $t->getMessage());
}

// 6) Cleanup: delete created campus
if ($createdId) {
    $resDelete = $controller->destroy((int)$createdId);
    $statusDelete = $resDelete->getStatusCode();
    out('delete_status', $statusDelete);
}

out('status', 'done');
