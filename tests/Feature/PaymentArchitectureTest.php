<?php

namespace Tests\Feature;

use App\Models\ChequePermission;
use App\Models\ChequePayment;
use App\Models\FinancialTransaction;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Models\WholesaleProfile;
use App\Services\ChequePaymentService;
use App\Services\ZarinPalPaymentGateway;
use App\Services\OnlinePaymentMethod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class PaymentArchitectureTest extends TestCase
{
    use RefreshDatabase;

    public function test_cheque_requires_approved_wholesale_and_explicit_permission(): void
    {
        $customer = User::factory()->create();

        $order = $this->orderFor($customer, 100000);

        try {
            app(ChequePaymentService::class)->submit(
                $order,
                $customer,
                [
                    'sayad_id' => '1234567890123456',
                    'bank_name' => 'Test Bank',
                    'due_date' => now()->addDays(10)->toDateString(),
                ]
            );

            $this->fail('Cheque submission should have been rejected.');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }
    }

    public function test_online_wholesale_checkout_also_requires_wholesale_approval(): void
    {
        $customer = User::factory()->create();

        try {
            app(OnlinePaymentMethod::class)->validateCheckout(
                $customer,
                ['order_type' => 'wholesale']
            );

            $this->fail('Wholesale approval should be required.');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }
    }

    public function test_approved_customer_can_submit_only_one_cheque_for_an_order(): void
    {
        $customer = User::factory()->create();

        WholesaleProfile::create([
            'user_id' => $customer->id,
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        ChequePermission::create([
            'user_id' => $customer->id,
            'enabled' => true,
            'max_order_amount' => 200000,
            'approved_at' => now(),
        ]);

        $order = $this->orderFor($customer, 100000);

        $service = app(ChequePaymentService::class);

        $payment = $service->submit(
            $order,
            $customer,
            [
                'sayad_id' => '1234567890123456',
                'bank_name' => 'Test Bank',
                'due_date' => now()->addDays(10)->toDateString(),
            ]
        );

        $this->assertSame('cheque', $payment->gateway);
        $this->assertSame('pending', $payment->status);
        $this->assertSame('submitted', $order->fresh()->chequePayment->status);

        try {
            $service->submit(
                $order->fresh(),
                $customer,
                [
                    'sayad_id' => '1234567890123457',
                    'bank_name' => 'Test Bank',
                    'due_date' => now()->addDays(10)->toDateString(),
                ]
            );

            $this->fail('Duplicate cheque submission should have been rejected.');
        } catch (HttpException $e) {
            $this->assertSame(409, $e->getStatusCode());
        }
    }

    public function test_cheque_must_be_cleared_before_becoming_a_paid_payment(): void
    {
        $admin = User::factory()->create()->forceFill(['is_admin' => true]);
        $admin->save();

        $customer = User::factory()->create();

        WholesaleProfile::create([
            'user_id' => $customer->id,
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        ChequePermission::create([
            'user_id' => $customer->id,
            'enabled' => true,
            'max_order_amount' => 200000,
            'approved_at' => now(),
        ]);

        $order = $this->orderFor($customer, 100000);

        $service = app(ChequePaymentService::class);

        $service->submit(
            $order,
            $customer,
            [
                'sayad_id' => '1234567890123456',
                'bank_name' => 'Test Bank',
                'due_date' => now()->addDays(10)->toDateString(),
            ]
        );

        $cheque = $order->fresh()->chequePayment;

        $service->accept($cheque, $admin);
        $this->assertSame('accepted', $cheque->fresh()->status);
        $this->assertSame('confirmed', $order->fresh()->status);
        $this->assertSame('pending', $order->fresh()->payment_status);

        $service->markDeposited($cheque->fresh(), $admin);
        $this->assertSame('deposited', $cheque->fresh()->status);

        $service->markCleared($cheque->fresh(), $admin);

        $this->assertSame('cleared', $cheque->fresh()->status);
        $this->assertSame('paid', $order->fresh()->payment_status);
        $this->assertDatabaseHas('financial_transactions', [
            'type' => 'income',
            'reference_type' => Order::class,
            'reference_id' => $order->id,
        ]);
    }

    public function test_zarinpal_gateway_uses_request_and_verify_provider_contract(): void
    {
        Http::fake([
            'https://api.zarinpal.com/pg/v4/payment/request.json' => Http::response([
                'data' => [
                    'code' => 100,
                    'authority' => 'AUTHORITY-123',
                ],
            ]),
            'https://api.zarinpal.com/pg/v4/payment/verify.json' => Http::response([
                'data' => [
                    'code' => 100,
                    'ref_id' => 'REF-123',
                ],
            ]),
        ]);

        config([
            'payment.zarinpal.merchant_id' => 'merchant-test',
            'payment.currency_unit' => 'toman',
        ]);

        $customer = User::factory()->create();
        $order = $this->orderFor($customer, 100000);

        $gateway = app(ZarinPalPaymentGateway::class);

        $payment = $gateway->purchase($order);

        $this->assertSame('zarinpal', $payment->gateway);
        $this->assertSame('AUTHORITY-123', $payment->authority);
        $this->assertStringContainsString(
            'AUTHORITY-123',
            (string) data_get($payment->metadata, 'redirect_url')
        );

        $verified = $gateway->verify($payment);

        $this->assertSame('paid', $verified->status);
        $this->assertSame('REF-123', $verified->reference_number);
    }

    private function orderFor(User $customer, float $total): Order
    {
        $order = Order::create([
            'user_id' => $customer->id,
            'order_number' => 'TEST-' . fake()->unique()->numerify('######'),
            'customer_name' => $customer->name,
            'customer_phone' => '09120000000',
            'shipping_address' => 'Test address',
            'status' => 'pending',
            'payment_status' => 'pending',
            'payment_method' => 'cheque',
            'order_type' => 'wholesale',
            'subtotal' => $total,
            'discount' => 0,
            'shipping_cost' => 0,
            'total' => $total,
            'placed_at' => now(),
        ]);

        $order->payments()->create([
            'gateway' => 'cheque',
            'idempotency_key' => 'seed:cheque:' . $order->id,
            'amount' => $total,
            'status' => 'pending',
        ]);

        return $order->fresh(['payments']);
    }
}
