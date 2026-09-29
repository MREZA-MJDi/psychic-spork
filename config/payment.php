<?php

return [
    'driver' => env('PAYMENT_DRIVER', 'zarinpal'),

    'currency_unit' => env('PAYMENT_CURRENCY_UNIT', 'toman'),

    'zarinpal' => [
        'merchant_id' => env('ZARINPAL_MERCHANT_ID'),
        'sandbox' => (bool) env('ZARINPAL_SANDBOX', false),
        'timeout' => (int) env('ZARINPAL_TIMEOUT', 15),
        'request_url' => env(
            'ZARINPAL_REQUEST_URL',
            'https://api.zarinpal.com/pg/v4/payment/request.json'
        ),
        'verify_url' => env(
            'ZARINPAL_VERIFY_URL',
            'https://api.zarinpal.com/pg/v4/payment/verify.json'
        ),
        'reverse_url' => env(
            'ZARINPAL_REVERSE_URL',
            'https://api.zarinpal.com/pg/v4/payment/reverse.json'
        ),
        'startpay_url' => env(
            'ZARINPAL_STARTPAY_URL',
            'https://www.zarinpal.com/pg/StartPay'
        ),
    ],
];
