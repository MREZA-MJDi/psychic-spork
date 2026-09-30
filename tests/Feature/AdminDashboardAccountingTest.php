<?php

namespace Tests\Feature;

use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\LedgerAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardAccountingTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_control_centers_are_reachable(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        foreach ([
            'admin.dashboard',
            'admin.products.index',
            'admin.categories.index',
            'admin.brands.index',
            'admin.customers.index',
            'admin.orders.index',
            'admin.inventory.index',
            'admin.wholesale.index',
            'admin.cheques.index',
            'admin.nila.index',
            'admin.accounting.index',
            'admin.content.about',
            'admin.contact.index',
            'admin.profile.edit',
        ] as $route) {
            $this->actingAs($admin)
                ->get(route($route))
                ->assertOk();
        }
    }

    public function test_dashboard_reads_revenue_from_double_entry_ledger(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $sales = LedgerAccount::create([
            'code' => 'sales',
            'name' => 'فروش',
            'type' => 'revenue',
            'is_active' => true,
        ]);

        $cash = LedgerAccount::create([
            'code' => 'cash',
            'name' => 'صندوق',
            'type' => 'asset',
            'is_active' => true,
        ]);

        $entry = JournalEntry::create([
            'entry_number' => 'JE-TEST-DASHBOARD',
            'source_key' => 'test-dashboard-sale',
            'entry_date' => now()->toDateString(),
            'description' => 'فروش تست داشبورد',
        ]);

        JournalLine::create([
            'journal_entry_id' => $entry->id,
            'ledger_account_id' => $cash->id,
            'debit' => 1250000,
            'credit' => 0,
        ]);

        JournalLine::create([
            'journal_entry_id' => $entry->id,
            'ledger_account_id' => $sales->id,
            'debit' => 0,
            'credit' => 1250000,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('۱٬۲۵۰٬۰۰۰');
    }

    public function test_accounting_admin_surfaces_double_entry_foundation(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->get(route('admin.accounting.index'))
            ->assertOk()
            ->assertSee('دفتر کل')
            ->assertSee('آخرین اسناد حسابداری');
    }
}
