<?php

return [
    'pdf' => [
        'enabled' => true,
        // Override with .env WKHTMLTOPDF_BINARY if installed elsewhere
        // Example (Windows): WKHTMLTOPDF_BINARY="C:\Program Files\wkhtmltopdf\bin\wkhtmltopdf.exe"
        'binary'  => env('WKHTMLTOPDF_BINARY', 'C:\\xampp8\\wkhtmltopdf\\bin\\wkhtmltopdf.exe'),
        'timeout' => false,
        'options' => [
            'encoding'      => 'utf-8',
            'margin-top'    => '12mm',
            'margin-right'  => '12mm',
            'margin-bottom' => '12mm',
            'margin-left'   => '12mm',
        ],
        'env' => [],
    ],
    'image' => [
        'enabled' => true,
        // Override with .env WKHTMLTOIMAGE_BINARY if installed elsewhere
        // Example (Windows): WKHTMLTOIMAGE_BINARY="C:\Program Files\wkhtmltopdf\bin\wkhtmltoimage.exe"
        'binary'  => env('WKHTMLTOIMAGE_BINARY', "C:\\xampp8\\wkhtmltopdf\\bin\\wkhtmltoimage.exe"),
        'timeout' => false,
        'options' => [
            'encoding' => 'utf-8',
        ],
        'env' => [],
    ],
];
