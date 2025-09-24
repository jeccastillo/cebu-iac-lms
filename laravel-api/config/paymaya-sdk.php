<?php

use Lloricode\Paymaya\PaymayaClient;
use Lloricode\Paymaya\Request\Webhook\Webhook;

return [
    // Default to SANDBOX to avoid null config during provider registration.
    'mode' => env('PAYMAYA_MODE', PaymayaClient::ENVIRONMENT_SANDBOX),

    // Provide string defaults to satisfy provider constructor type hints.
    'keys' => [
        'public' => env('PAYMAYA_PUBLIC_KEY', ''),
        'secret' => env('PAYMAYA_SECRET_KEY', ''),
    ],

    // Webhook endpoints (can be overridden via env).
    'webhooks' => [
        Webhook::CHECKOUT_SUCCESS => env('PAYMAYA_WEBHOOK_SUCCESS', 'api/payment-callback/paymaya/success'),
        Webhook::CHECKOUT_FAILURE => env('PAYMAYA_WEBHOOK_FAILURE', 'api/payment-callback/paymaya/failure'),
        Webhook::CHECKOUT_DROPOUT => env('PAYMAYA_WEBHOOK_DROPOUT', 'api/payment-callback/paymaya/dropout'),
        // Webhook::PAYMENT_SUCCESS => 'api/test/success',
        // Webhook::PAYMENT_EXPIRED => 'api/test/expired',
        // Webhook::PAYMENT_FAILED => 'api/test/failed',
    ],

    'checkout' => [
        'customization' => [
            'logoUrl' => env('PAYMAYA_CHECKOUT_LOGO_URL', ''),
            'iconUrl' => env('PAYMAYA_CHECKOUT_ICON_URL', ''),
            'appleTouchIconUrl' => env('PAYMAYA_CHECKOUT_APPLE_TOUCH_ICON_URL', ''),
            'customTitle' => env('PAYMAYA_CHECKOUT_TITLE', 'PayMaya Checkout'),
            'colorScheme' => env('PAYMAYA_CHECKOUT_COLOR', '#e01c44'),
            'redirectTimer' => (int) env('PAYMAYA_CHECKOUT_REDIRECT_TIMER', 3),
        ],
    ],
];
