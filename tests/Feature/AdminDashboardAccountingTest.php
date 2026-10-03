<?php

namespace Tests\Feature;

use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\LedgerAccount;
use App\Models\FinancialTransaction;
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

    public function test_dashboard_uses_ledger_refunds_once_for_revenue_and_net_cash(): void
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

        $returns = LedgerAccount::create([
            'code' => 'sales_returns',
            'name' => 'برگشت از فروش',
            'type' => 'revenue',
            'is_active' => true,
        ]);

        $entry = JournalEntry::create([
            'entry_number' => 'JE-TEST-DASHBOARD',
            'source_key' => 'test-dashboard-sale',
            'entry_date' => now()->subDays(40)->toDateString(),
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

        $refund = JournalEntry::create([
            'entry_number' => 'JE-TEST-DASHBOARD-REFUND',
            'source_key' => 'test-dashboard-refund',
            'entry_date' => now()->toDateString(),
            'description' => 'بازپرداخت تست داشبورد',
        ]);

        JournalLine::create([
            'journal_entry_id' => $refund->id,
            'ledger_account_id' => $returns->id,
            'debit' => 250000,
            'credit' => 0,
        ]);

        JournalLine::create([
            'journal_entry_id' => $refund->id,
            'ledger_account_id' => $cash->id,
            'debit' => 0,
            'credit' => 250000,
        ]);

        FinancialTransaction::create([
            'type' => 'expense',
            'category' => 'refund',
            'amount' => 250000,
            'description' => 'ردیف legacy بازپرداخت',
            'transaction_date' => now()->toDateString(),
        ]);

        FinancialTransaction::create([
            'type' => 'expense',
            'category' => 'operations',
            'amount' => 100000,
            'description' => 'هزینه دستی تست',
            'transaction_date' => now()->toDateString(),
        ]);

        $this->assertDatabaseHas('financial_transactions', [
            'type' => 'expense',
            'category' => 'operations',
            'amount' => 100000,
        ]);

        $this->assertSame(100000.0, (float) FinancialTransaction::query()
            ->where('type', 'expense')
            ->where(fn ($query) => $query
                ->whereNull('category')
                ->orWhere('category', '!=', 'refund'))
            ->whereDate('transaction_date', '>=', now()->startOfDay()->subDays(29)->toDateString())
            ->whereDate('transaction_date', '<=', now()->endOfDay()->toDateString())
            ->sum('amount'));

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertViewHas('expenses', 100000.0)
            ->assertViewHas('revenue', -250000.0)
            ->assertViewHas('netCash', -350000.0)
            ->assertViewHas('daily', function ($daily): bool {
                $today = $daily->firstWhere('date', now()->toDateString());

                return $today !== null
                    && (float) $today['income'] === -250000.0;
            })
            ->assertSee('−۰٫۳M')
            ->assertSee('-۲۵۰٬۰۰۰')
            ->assertSee('-۳۵۰٬۰۰۰');
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
