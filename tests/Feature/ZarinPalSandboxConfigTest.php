<?php

namespace Tests\Feature;

use Tests\TestCase;

class ZarinPalSandboxConfigTest extends TestCase
{
    public function test_sandbox_flag_selects_the_official_zarinpal_sandbox_endpoints(): void
    {
        $originalEnv = $_ENV['ZARINPAL_SANDBOX'] ?? null;
        $originalServer = $_SERVER['ZARINPAL_SANDBOX'] ?? null;
        $hadEnv = array_key_exists('ZARINPAL_SANDBOX', $_ENV);
        $hadServer = array_key_exists('ZARINPAL_SANDBOX', $_SERVER);

        try {
            $_ENV['ZARINPAL_SANDBOX'] = 'true';
            $_SERVER['ZARINPAL_SANDBOX'] = 'true';

            $config = require config_path('payment.php');

            $this->assertTrue($config['zarinpal']['sandbox']);
            $this->assertSame(
                'https://sandbox.zarinpal.com/pg/v4/payment/request.json',
                $config['zarinpal']['request_url']
            );
            $this->assertSame(
                'https://sandbox.zarinpal.com/pg/v4/payment/verify.json',
                $config['zarinpal']['verify_url']
            );
            $this->assertSame(
                'https://sandbox.zarinpal.com/pg/v4/payment/reverse.json',
                $config['zarinpal']['reverse_url']
            );
            $this->assertSame(
                'https://sandbox.zarinpal.com/pg/StartPay',
                $config['zarinpal']['startpay_url']
            );
        } finally {
            if ($hadEnv) {
                $_ENV['ZARINPAL_SANDBOX'] = $originalEnv;
            } else {
                unset($_ENV['ZARINPAL_SANDBOX']);
            }

            if ($hadServer) {
                $_SERVER['ZARINPAL_SANDBOX'] = $originalServer;
            } else {
                unset($_SERVER['ZARINPAL_SANDBOX']);
            }
        }
    }

    public function test_sandbox_string_false_does_not_enable_sandbox(): void
    {
        $originalEnv = $_ENV['ZARINPAL_SANDBOX'] ?? null;
        $originalServer = $_SERVER['ZARINPAL_SANDBOX'] ?? null;
        $hadEnv = array_key_exists('ZARINPAL_SANDBOX', $_ENV);
        $hadServer = array_key_exists('ZARINPAL_SANDBOX', $_SERVER);

        try {
            $_ENV['ZARINPAL_SANDBOX'] = 'false';
            $_SERVER['ZARINPAL_SANDBOX'] = 'false';

            $config = require config_path('payment.php');

            $this->assertFalse($config['zarinpal']['sandbox']);
            $this->assertSame(
                'https://payment.zarinpal.com/pg/v4/payment/request.json',
                $config['zarinpal']['request_url']
            );
            $this->assertSame(
                'https://payment.zarinpal.com/pg/StartPay',
                $config['zarinpal']['startpay_url']
            );
        } finally {
            if ($hadEnv) {
                $_ENV['ZARINPAL_SANDBOX'] = $originalEnv;
            } else {
                unset($_ENV['ZARINPAL_SANDBOX']);
            }

            if ($hadServer) {
                $_SERVER['ZARINPAL_SANDBOX'] = $originalServer;
            } else {
                unset($_SERVER['ZARINPAL_SANDBOX']);
            }
        }
    }
}
