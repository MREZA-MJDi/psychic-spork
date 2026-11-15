<?php

$sandbox = filter_var(
    env('ZARINPAL_SANDBOX', false),
    FILTER_VALIDATE_BOOL
);

$apiBaseUrl = $sandbox
    ? 'https://sandbox.zarinpal.com'
    : 'https://payment.zarinpal.com';

// Never let production endpoint overrides leak into sandbox mode.
$requestUrl = $apiBaseUrl . '/pg/v4/payment/request.json';
$verifyUrl = $apiBaseUrl . '/pg/v4/payment/verify.json';
$reverseUrl = $apiBaseUrl . '/pg/v4/payment/reverse.json';
$startpayUrl = $apiBaseUrl . '/pg/StartPay';

if (! $sandbox) {
    $requestUrl = env('ZARINPAL_REQUEST_URL', $requestUrl);
    $verifyUrl = env('ZARINPAL_VERIFY_URL', $verifyUrl);
    $reverseUrl = env('ZARINPAL_REVERSE_URL', $reverseUrl);
    $startpayUrl = env('ZARINPAL_STARTPAY_URL', $startpayUrl);
}

return [
    'driver' => env('PAYMENT_GATEWAY', 'zarinpal'),

    'currency_unit' => env('PAYMENT_CURRENCY_UNIT', 'toman'),

    'zarinpal' => [
        'merchant_id' => env('ZARINPAL_MERCHANT_ID'),
        'sandbox' => $sandbox,
        'timeout' => (int) env('ZARINPAL_TIMEOUT', 15),
        'request_url' => $requestUrl,
        'verify_url' => $verifyUrl,
        'reverse_url' => $reverseUrl,
        'startpay_url' => $startpayUrl,
    ],
];
