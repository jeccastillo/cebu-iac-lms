<?php
$f = __DIR__ . '/../app/Http/Controllers/Api/V1/ReportsController.php';
$start = 480;
$end = 540;
$lines = file($f, FILE_IGNORE_NEW_LINES);
$max = count($lines);
for ($i = $start; $i <= $end && $i <= $max; $i++) {
    $line = $lines[$i - 1];
    echo str_pad($i, 5, ' ', STR_PAD_LEFT) . ': ' . $line . PHP_EOL;
}
