<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\ChequePayment;
use App\Models\ContactMessage;
use App\Models\FinancialTransaction;
use App\Models\IntegrationMapping;
use App\Models\JournalLine;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\WholesaleProfile;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(Request $request): View
    {
        /*
        |--------------------------------------------------------------------------
        | Selected period
        |--------------------------------------------------------------------------
        */

        $period = max(
            7,
            min($request->integer('period', 30), 90)
        );

        $from = now()
            ->startOfDay()
            ->subDays($period - 1);

        $to = now()->endOfDay();


        /*
        |--------------------------------------------------------------------------
        | Orders in selected period
        |--------------------------------------------------------------------------
        */

        $baseOrders = Order::query()
            ->whereBetween('placed_at', [$from, $to]);


        /*
        |--------------------------------------------------------------------------
        | Accounting summary
        |
        | The dashboard reads recognized revenue and settled cash/bank
        | from the double-entry ledger. Legacy manual expenses remain
        | visible until the manual-expense posting path is migrated.
        |--------------------------------------------------------------------------
        */

        $ledgerLines = JournalLine::query()
            ->with('account')
            ->whereHas('entry', function ($query) use ($from, $to): void {
                $query
                    ->whereDate('entry_date', '>=', $from->toDateString())
                    ->whereDate('entry_date', '<=', $to->toDateString());
            });

        $revenue = (float) (clone $ledgerLines)
            ->whereHas('account', fn ($query) => $query->where('code', 'sales'))
            ->sum('credit');

        $salesReturns = (float) (clone $ledgerLines)
            ->whereHas('account', fn ($query) => $query->where('code', 'sales_returns'))
            ->sum('debit');

        $revenue = max(0, $revenue - $salesReturns);

        $settledFundsIn = (float) (clone $ledgerLines)
            ->whereHas('account', fn ($query) => $query->whereIn('code', ['cash', 'bank']))
            ->sum('debit');

        $settledFundsOut = (float) (clone $ledgerLines)
            ->whereHas('account', fn ($query) => $query->whereIn('code', ['cash', 'bank']))
            ->sum('credit');

        $settledFunds = $settledFundsIn - $settledFundsOut;

        $expenses = (float) FinancialTransaction::query()
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [
                $from->toDateString(),
                $to->toDateString(),
            ])
            ->sum('amount');

        $netCash = $settledFunds - $expenses;


        /*
        |--------------------------------------------------------------------------
        | Daily sales chart
        |--------------------------------------------------------------------------
        */

        $rows = (clone $baseOrders)
            ->selectRaw(
                "DATE(placed_at) as day,
                COUNT(*) as orders,
                COALESCE(
                    SUM(
                        CASE
                            WHEN payment_status = 'paid'
                            THEN total
                            ELSE 0
                        END
                    ),
                    0
                ) as income"
            )
            ->groupByRaw('DATE(placed_at)')
            ->get()
            ->keyBy('day');

        $daily = collect(range(0, $period - 1))
            ->map(function (int $index) use ($from, $rows): array {
                $day = $from->copy()->addDays($index);

                $row = $rows->get(
                    $day->toDateString()
                );

                return [
                    'label' => $day->format('m/d'),
                    'date' => $day->toDateString(),

                    'income' => (float) (
                        $row->income ?? 0
                    ),

                    'orders' => (int) (
                        $row->orders ?? 0
                    ),
                ];
            });


        /*
        |--------------------------------------------------------------------------
        | Inventory
        |--------------------------------------------------------------------------
        */

        $lowStockVariants = ProductVariant::query()
            ->with([
                'product:id,name',
            ])
            ->where('is_active', true)
            ->whereColumn(
                'stock',
                '<=',
                'low_stock_threshold'
            )
            ->orderBy('stock')
            ->orderBy('id')
            ->limit(6)
            ->get();

        $lowStock = ProductVariant::query()
            ->where('is_active', true)
            ->whereColumn(
                'stock',
                '<=',
                'low_stock_threshold'
            )
            ->count();

        $inventoryValue = (float) ProductVariant::query()
            ->where('is_active', true)
            ->selectRaw(
                'COALESCE(
                    SUM(
                        stock * COALESCE(sale_price, price)
                    ),
                    0
                ) as total'
            )
            ->value('total');


        /*
        |--------------------------------------------------------------------------
        | Dashboard calculations
        |--------------------------------------------------------------------------
        */

        $dailyMaxIncome = (float) $daily->max('income');

        $paidOrdersCount = (clone $baseOrders)
            ->where('payment_status', 'paid')
            ->count();

        $ordersCount = (clone $baseOrders)
            ->count();

        $orderBreakdown = (clone $baseOrders)
            ->selectRaw(
                'status, COUNT(*) as total'
            )
            ->groupBy('status')
            ->pluck('total', 'status');


        /*
        |--------------------------------------------------------------------------
        | Current processing orders
        |
        | IMPORTANT:
        | This is intentionally NOT limited to the selected period.
        | "Current processing" means orders whose current status is
        | pending / confirmed / preparing, regardless of when they
        | were originally placed.
        |--------------------------------------------------------------------------
        */

        $pendingOrders = Order::query()
            ->activeProcessing()
            ->count();


        /*
        |--------------------------------------------------------------------------
        | Customers
        |--------------------------------------------------------------------------
        */

        $customers = User::query()
            ->customers()
            ->count();

        $unreadContactMessages = ContactMessage::query()
            ->unread()
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Catalog control metrics
        |--------------------------------------------------------------------------
        */

        $catalogProducts = Product::query()->count();
        $catalogBrands = Brand::query()->count();
        $catalogCategories = Category::query()->count();
        $catalogVariants = ProductVariant::query()->where('is_active', true)->count();
        $catalogWholesalePricedVariants = ProductVariant::query()
            ->where('is_active', true)
            ->whereNotNull('wholesale_price')
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Management queues
        |--------------------------------------------------------------------------
        */

        $chequesAwaitingReview = ChequePayment::query()
            ->whereIn('status', ['submitted', 'under_review'])
            ->count();

        $chequesAwaitingReviewAmount = (float) ChequePayment::query()
            ->whereIn('status', ['submitted', 'under_review'])
            ->sum('amount');

        $pendingWholesaleApplications = WholesaleProfile::query()
            ->where('status', 'pending')
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Nila mapping metrics
        |--------------------------------------------------------------------------
        */

        $nilaMappings = IntegrationMapping::query()
            ->where('integration', 'nila');

        $nilaProductMappings = (clone $nilaMappings)
            ->where('entity_type', Product::class)
            ->count();

        $nilaVariantMappings = (clone $nilaMappings)
            ->where('entity_type', ProductVariant::class)
            ->count();

        $nilaLastMappedAt = (clone $nilaMappings)
            ->latest('updated_at')
            ->value('updated_at');


        /*
        |--------------------------------------------------------------------------
        | Recent orders
        |
        | This is intentionally global/latest, not period-limited.
        |--------------------------------------------------------------------------
        */

        $recentOrders = Order::query()
            ->with('user')
            ->latest('placed_at')
            ->latest('id')
            ->limit(7)
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Recent products
        |--------------------------------------------------------------------------
        |
        | Keep catalog visibility separate from sales analytics.
        | Newly created products should be visible even before they sell.
        |--------------------------------------------------------------------------
        */

        $recentProducts = Product::query()
            ->with([
                'category:id,name',
                'galleryMedia',
                'variants',
            ])
            ->latest('created_at')
            ->latest('id')
            ->limit(6)
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Top products
        |
        | NOTE:
        | Currently calculated across all order items.
        | We will only make this period-aware after checking OrderItem
        | so cancelled/returned orders are handled correctly.
        |--------------------------------------------------------------------------
        */
        $topProductSalesFilter = function ($query) use ($from, $to) {
            $query->whereHas('order', function ($orderQuery) use ($from, $to) {
                $orderQuery
                    ->whereBetween('placed_at', [$from, $to])
                    ->whereNotIn(
                        'status',
                        Order::CANCEL_LIKE_STATUSES
                    )
                    ->where('payment_status', 'paid');
            });
        };

        $topProducts = Product::query()
            ->with([
                'category:id,name',
            ])
            ->whereHas('orderItems', $topProductSalesFilter)
            ->withSum(
                [
                    'orderItems as sales_quantity' => $topProductSalesFilter,
                ],
                'quantity'
            )
            ->orderByDesc('sales_quantity')
            ->orderBy('id')
            ->limit(6)
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Status labels
        |--------------------------------------------------------------------------
        */

        $statusNames = [
            'pending' => 'در انتظار',
            'confirmed' => 'تأیید شده',
            'preparing' => 'در حال آماده‌سازی',
            'shipped' => 'ارسال شده',
            'delivered' => 'تحویل شده',
            'cancelled' => 'لغو شده',
            'returned' => 'مرجوعی',
        ];


        /*
        |--------------------------------------------------------------------------
        | View
        |--------------------------------------------------------------------------
        */

        return view(
            'admin.dashboard.index',
            [
                'period' => $period,

                'revenue' => $revenue,

                'expenses' => $expenses,

                'netCash' => $netCash,

                'settledFunds' => $settledFunds,

                'salesReturns' => $salesReturns,

                'paidOrdersCount' => $paidOrdersCount,

                'ordersCount' => $ordersCount,

                'pendingOrders' => $pendingOrders,

                'customers' => $customers,

                'unreadContactMessages' => $unreadContactMessages,

                'catalogProducts' => $catalogProducts,
                'catalogBrands' => $catalogBrands,
                'catalogCategories' => $catalogCategories,
                'catalogVariants' => $catalogVariants,
                'catalogWholesalePricedVariants' => $catalogWholesalePricedVariants,

                'chequesAwaitingReview' => $chequesAwaitingReview,
                'chequesAwaitingReviewAmount' => $chequesAwaitingReviewAmount,
                'pendingWholesaleApplications' => $pendingWholesaleApplications,

                'nilaProductMappings' => $nilaProductMappings,
                'nilaVariantMappings' => $nilaVariantMappings,
                'nilaLastMappedAt' => $nilaLastMappedAt,

                'lowStock' => $lowStock,

                'inventoryValue' => $inventoryValue,

                'daily' => $daily,

                'maxIncome' => max(
                    1,
                    $dailyMaxIncome
                ),

                'orderBreakdown' => $orderBreakdown,

                'statusNames' => $statusNames,

                'lowStockVariants' => $lowStockVariants,

                'recentOrders' => $recentOrders,

                'topProducts' => $topProducts,

                'recentProducts' => $recentProducts,
            ]
        );
    }
}
