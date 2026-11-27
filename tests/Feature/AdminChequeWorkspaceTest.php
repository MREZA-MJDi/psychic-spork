<?php

namespace Tests\Feature;

use App\Models\ChequePermission;
use App\Models\Order;
use App\Models\User;
use App\Services\ChequePaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminChequeWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_selected_cheque_has_only_current_stage_actions(): void
    {
        $this->withoutVite();
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create();
        ChequePermission::create(['user_id' => $customer->id, 'enabled' => true, 'approved_at' => now(), 'max_order_amount' => 200000]);
        $order = Order::create([
            'user_id' => $customer->id, 'order_number' => 'UI-CHEQUE-001',
            'customer_name' => $customer->name, 'customer_phone' => '09120000000',
            'shipping_address' => 'Test address', 'status' => 'pending',
            'payment_status' => 'pending', 'payment_method' => 'cheque', 'order_type' => 'wholesale',
            'subtotal' => 100000, 'discount' => 0, 'shipping_cost' => 0, 'total' => 100000, 'placed_at' => now(),
        ]);
        app(ChequePaymentService::class)->submit($order, $customer, [
            'sayad_id' => '1234567890123456', 'bank_name' => 'Test Bank',
            'cheque_number' => 'QA-123', 'due_date' => '2026-12-22',
        ]);
        $cheque = $order->fresh()->chequePayment;
        $url = route('admin.cheques.index', ['cheque_id' => $cheque->id]);
        $this->actingAs($customer)->get($url)->assertForbidden();
        $this->actingAs($admin)->get(route('admin.cheques.index'))->assertOk()
            ->assertSee('QA-123')->assertDontSee('id="cheque-detail"', false)
            ->assertDontSee('action="' . route('admin.cheques.review', $cheque) . '"', false);
        $this->get($url)->assertOk()
            ->assertSee('id="cheque-detail"', false)
            ->assertSee('data-admin-date="2026-12-22"', false)
            ->assertSee(route('admin.cheques.review', $cheque))
            ->assertDontSee('action="' . route('admin.cheques.clear', $cheque) . '"', false);
        $this->from($url)->patch(route('admin.cheques.review', $cheque))->assertRedirect($url);
        $this->get($url)->assertOk()
            ->assertSee(route('admin.cheques.accept', $cheque))
            ->assertSee(route('admin.cheques.request-original', $cheque))
            ->assertSee(route('admin.cheques.reject', $cheque))
            ->assertDontSee('action="' . route('admin.cheques.clear', $cheque) . '"', false);
        $this->from($url)->patch(route('admin.cheques.request-original', $cheque), ['note' => 'Send original to office'])->assertRedirect($url);
        $this->get($url)->assertOk()->assertSee(route('admin.cheques.original-received', $cheque));
        $this->from($url)->patch(route('admin.cheques.original-received', $cheque))->assertRedirect($url);
        $this->from($url)->patch(route('admin.cheques.accept', $cheque))->assertRedirect($url);
        $this->assertSame('pending', $order->fresh()->payment_status);
        $this->get($url)->assertOk()->assertSee(route('admin.cheques.deposit', $cheque));
        $this->from($url)->patch(route('admin.cheques.deposit', $cheque))->assertRedirect($url);
        $this->get($url)->assertOk()->assertSee(route('admin.cheques.clear', $cheque))->assertSee(route('admin.cheques.bounce', $cheque));
        $this->from($url)->patch(route('admin.cheques.clear', $cheque))->assertRedirect($url);
        $this->get($url)->assertOk()->assertDontSee('action="' . route('admin.cheques.clear', $cheque) . '"', false);
        $this->assertSame('cleared', $cheque->fresh()->status);
    }

    public function test_permission_editor_is_only_rendered_for_selected_customer(): void
    {
        $this->withoutVite();
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create();
        ChequePermission::create(['user_id' => $customer->id, 'enabled' => false, 'requested_at' => now(), 'requested_amount' => 500000]);
        $this->actingAs($admin)->get(route('admin.cheques.index', ['tab' => 'requests']))->assertOk()
            ->assertDontSee('name="max_order_amount"', false);
        $this->get(route('admin.cheques.index', ['tab' => 'requests', 'edit_customer' => $customer->id]))->assertOk()
            ->assertSee('name="max_order_amount"', false)->assertSee(route('admin.customers.cheque.enable', $customer));
    }
}
