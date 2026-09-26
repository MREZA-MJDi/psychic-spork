<?php

return [
    'driver' => env('PAYMENT_GATEWAY', 'zarinpal'),

    'zarinpal' => [
        'merchant_id' => env('ZARINPAL_MERCHANT_ID'),
        'request_url' => env(
            'ZARINPAL_REQUEST_URL',
            'https://api.zarinpal.com/pg/v4/payment/request.json'
        ),
        'verify_url' => env(
            'ZARINPAL_VERIFY_URL',
            'https://api.zarinpal.com/pg/v4/payment/verify.json'
        ),
        'startpay_url' => env(
            'ZARINPAL_STARTPAY_URL',
            'https://www.zarinpal.com/pg/StartPay'
        ),
        'timeout' => (int) env('ZARINPAL_TIMEOUT', 15),
    ],
];
