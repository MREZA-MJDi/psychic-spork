<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FinancialTransaction;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
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
        | Financial summary
        |--------------------------------------------------------------------------
        */

        $revenue = (float) (clone $baseOrders)
            ->where('payment_status', 'paid')
            ->sum('total');

        $expenses = (float) FinancialTransaction::query()
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [
                $from->toDateString(),
                $to->toDateString(),
            ])
            ->sum('amount');


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
        | Top products
        |
        | NOTE:
        | Currently calculated across all order items.
        | We will only make this period-aware after checking OrderItem
        | so cancelled/returned orders are handled correctly.
        |--------------------------------------------------------------------------
        */
        $topProducts = Product::query()
            ->with([
                'category:id,name',
            ])
            ->withSum(
                [
                    'orderItems as sales_quantity' => function ($query) use ($from, $to) {
                        $query->whereHas('order', function ($orderQuery) use ($from, $to) {
                            $orderQuery
                                ->whereBetween('placed_at', [$from, $to])
                                ->whereNotIn(
                                    'status',
                                    Order::CANCEL_LIKE_STATUSES
                                )
                                ->where('payment_status', 'paid');
                        });
                    },
                ],
                'quantity'
            )
            ->having('sales_quantity', '>', 0)
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

                'netCash' => $revenue - $expenses,

                'paidOrdersCount' => $paidOrdersCount,

                'ordersCount' => $ordersCount,

                'pendingOrders' => $pendingOrders,

                'customers' => $customers,

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
            ]
        );
    }
}
