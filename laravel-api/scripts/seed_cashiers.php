<?php
/**
 * Seed a sample cashier row for testing the Cashier Admin feature.
 *
 * Usage:
 *   php laravel-api/scripts/seed_cashiers.php
 *
 * Notes:
 * - Creates a row with nullable user_id (no FK). Name in listing will be empty string.
 * - OR range: 100000 to 100099 (current=100000)
 * - Invoice range: 200000 to 200099 (current=200000)
 */

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use App\Models\Cashier;

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
// Boot kernel
$kernel->handle(Request::create('/health', 'GET'));

$count = Cashier::query()->count();
if ($count > 0) {
    echo "Cashiers already exist, count={$count}. No action taken.\n";
    exit(0);
}

$row = new Cashier();
$row->user_id = null;            // nullable, join will produce blank name
$row->campus_id = null;          // global
$row->temporary_admin = 0;

// OR range
$row->or_start = 100000;
$row->or_end = 100099;
$row->or_current = 100000;

// Invoice range
$row->invoice_start = 200000;
$row->invoice_end = 200099;
$row->invoice_current = 200000;

// Optional legacy counters
$row->or_used = null;
$row->invoice_used = null;

$row->save();

echo "Seeded sample cashier row id={$row->intID}\n";
