<?php

return [

    // Paynamics configuration
    'paynamics' => [
        'merchant_id' => env('PAYNAMICS_MERCHANT_ID', ''),
        'mkey'        => env('PAYNAMICS_MKEY', ''),
        'username'    => env('PAYNAMICS_USERNAME', ''),
        'password'    => env('PAYNAMICS_PASSWORD', ''),
        'url' => [
            'prod'    => env('PAYNAMICS_URL_PROD', 'https://payin.paynamics.net/paygate/transactions/'),
            'staging' => env('PAYNAMICS_URL_STAGING', 'https://payin.payserv.net/paygate/transactions/'),
        ],
        // Notification endpoints (webhooks)
        'webhook' => [
            'paynamics' => env('PAYNAMICS_WEBHOOK_URL', '/api/v1/payments/webhook/paynamics'),
        ],
    ],

    // BDO / CyberSource Secure Acceptance configuration
    'bdo' => [
        'access_key'  => env('BDO_ACCESS_KEY', ''),
        'profile_id'  => env('BDO_PROFILE_ID', ''),
        'secret_key'  => env('BDO_SECRET_KEY', ''),
        'url'         => env('BDO_URL', 'https://secureacceptance.cybersource.com/pay'),
        // Signed field template for sale transaction
        'signed_fields' => env('BDO_SIGNED_FIELDS', 'access_key,profile_id,transaction_uuid,signed_field_names,unsigned_field_names,signed_date_time,locale,transaction_type,reference_number,amount,currency,bill_to_address_line1,bill_to_address_city,bill_to_address_country,bill_to_email,bill_to_surname,bill_to_forename'),
        'transaction_type' => env('BDO_TRANSACTION_TYPE', 'sale'),
        'currency' => env('BDO_CURRENCY', 'PHP'),
        'locale' => env('BDO_LOCALE', 'en'),
        'bill_to' => [
            'address_line1'   => env('BDO_BILLTO_ADDRESS_LINE1', 'iACADEMY Nexxus Yakal St'),
            'address_city'    => env('BDO_BILLTO_ADDRESS_CITY', 'Makati City'),
            'address_country' => env('BDO_BILLTO_ADDRESS_COUNTRY', 'PH'),
        ],
        // Notification endpoints (webhooks)
        'webhook' => [
            'bdo' => env('BDO_WEBHOOK_URL', '/api/v1/payments/webhook/bdo'),
        ],
    ],

    // MaxxPayment configuration
    'maxx' => [
        'url' => [
            'prod'    => env('MAXX_URL_PROD', 'https://secure.maxxpayment.com/api/mp?live=1'),
            'staging' => env('MAXX_URL_STAGING', 'https://sandbox.maxxpayment.com/api/mp/?live=0'),
        ],
        'mc_code'      => env('MAXX_MC_CODE', 'SC000419'),
        'options_json' => env('MAXX_OPTIONS_JSON', '{"show_paymode":"1,2,3,4","show_payterm":"3,6,9,12,18,24"}'),
        // Notification endpoints (webhooks)
        'webhook' => [
            'maxx' => env('MAXX_WEBHOOK_URL', '/api/v1/payments/webhook/maxx'),
        ],
    ],

    // Frontend redirect URLs (handled by AngularJS routes)
    'frontend' => [
        'success_url' => env('PAY_SUCCESS_URL', '/#/payments/success'),
        'failure_url' => env('PAY_FAILURE_URL', '/#/payments/failure'),
        'cancel_url'  => env('PAY_CANCEL_URL', '/#/payments/cancel'),
    ],

    // Helper: environment selection (local/staging/production)
    'environment' => env('APP_ENV', 'local'),
];
