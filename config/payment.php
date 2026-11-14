<?php

$sandbox = filter_var(
    env('ZARINPAL_SANDBOX', false),
    FILTER_VALIDATE_BOOL
);

$apiBaseUrl = $sandbox
    ? 'https://sandbox.zarinpal.com'
    : 'https://payment.zarinpal.com';

$startPayBaseUrl = $sandbox
    ? 'https://sandbox.zarinpal.com'
    : 'https://www.zarinpal.com';

return [
    'driver' => env('PAYMENT_GATEWAY', 'zarinpal'),

    'currency_unit' => env('PAYMENT_CURRENCY_UNIT', 'toman'),

    'zarinpal' => [
        'merchant_id' => env('ZARINPAL_MERCHANT_ID'),
        'sandbox' => $sandbox,
        'timeout' => (int) env('ZARINPAL_TIMEOUT', 15),
        'request_url' => env(
            'ZARINPAL_REQUEST_URL',
            $apiBaseUrl . '/pg/v4/payment/request.json'
        ),
        'verify_url' => env(
            'ZARINPAL_VERIFY_URL',
            $apiBaseUrl . '/pg/v4/payment/verify.json'
        ),
        'reverse_url' => env(
            'ZARINPAL_REVERSE_URL',
            $apiBaseUrl . '/pg/v4/payment/reverse.json'
        ),
        'startpay_url' => env(
            'ZARINPAL_STARTPAY_URL',
            $startPayBaseUrl . '/pg/StartPay'
        ),
    ],
];
