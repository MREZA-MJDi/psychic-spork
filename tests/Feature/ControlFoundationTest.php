<?php

namespace Tests\Feature;

use App\Models\JournalEntry;
use App\Models\User;
use App\Models\WholesaleProfile;
use App\Models\ChequePermission;
use App\Services\DoubleEntryAccountingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ControlFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_cheque_permission_is_independent_from_wholesale_profile_status(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create(['is_admin' => false]);

        WholesaleProfile::create([
            'user_id' => $customer->id,
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.customers.cheque.enable', $customer), [
                'max_order_amount' => 50_000_000,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('cheque_permissions', [
            'user_id' => $customer->id,
            'enabled' => true,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.customers.wholesale.suspend', $customer))
            ->assertRedirect();

        $this->assertDatabaseHas('cheque_permissions', [
            'user_id' => $customer->id,
            'enabled' => true,
        ]);
    }

    public function test_double_entry_sale_is_balanced_and_idempotent(): void
    {
        $reference = User::factory()->create();

        $service = app(DoubleEntryAccountingService::class);

        $first = $service->recordSale(
            reference: $reference,
            amount: 1_250_000,
            settlementAccount: 'bank',
            sourceKey: 'test:sale:1',
            description: 'فروش تست',
        );

        $second = $service->recordSale(
            reference: $reference,
            amount: 1_250_000,
            settlementAccount: 'bank',
            sourceKey: 'test:sale:1',
            description: 'فروش تست',
        );

        $this->assertSame($first->id, $second->id);
        $this->assertDatabaseCount('journal_entries', 1);
        $this->assertDatabaseCount('journal_lines', 2);
        $this->assertEqualsWithDelta(
            1_250_000,
            (float) $first->lines->sum('debit'),
            0.01
        );
        $this->assertEqualsWithDelta(
            1_250_000,
            (float) $first->lines->sum('credit'),
            0.01
        );
    }

    public function test_nila_control_center_does_not_claim_live_api_sync(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->get(route('admin.nila.index'))
            ->assertOk()
            ->assertSee('Mapping')
            ->assertSee('قرارداد رسمی Nila');
    }
}
