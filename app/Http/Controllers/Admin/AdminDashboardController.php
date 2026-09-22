<?php

namespace App\Http\Controllers\Admin;

use App\Models\FinancialTransaction;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminDashboardController extends AdminController
{
    public function index(Request $request): View
    {
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
        | Daily chart
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
                $row = $rows->get($day->toDateString());

                return [
                    'label' => $day->format('m/d'),
                    'income' => (float) ($row->income ?? 0),
                    'orders' => (int) ($row->orders ?? 0),
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
                'COALESCE(SUM(stock * effective_price), 0) as total'
            )
            ->value('total');

        /*
        |--------------------------------------------------------------------------
        | Dashboard data
        |--------------------------------------------------------------------------
        */

        $dailyMaxIncome = (float) $daily->max('income');

        $paidOrdersCount = (clone $baseOrders)
            ->where('payment_status', 'paid')
            ->count();

        $ordersCount = (clone $baseOrders)
            ->count();

        $orderBreakdown = (clone $baseOrders)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $pendingOrders = (clone $baseOrders)
            ->activeProcessing()
            ->count();

        return view('admin.dashboard.index', [
            'period' => $period,

            'revenue' => $revenue,

            'expenses' => $expenses,

            'netCash' => $revenue - $expenses,

            'paidOrdersCount' => $paidOrdersCount,

            'ordersCount' => $ordersCount,

            'pendingOrders' => $pendingOrders,

            'lowStock' => $lowStock,

            'inventoryValue' => $inventoryValue,

            'customers' => User::customers()->count(),

            'daily' => $daily,

            'maxIncome' => max(
                1,
                $dailyMaxIncome
            ),

            'orderBreakdown' => $orderBreakdown,

            'statusNames' => [
                'pending' => 'در انتظار',
                'confirmed' => 'تأیید شده',
                'preparing' => 'در حال آماده‌سازی',
                'shipped' => 'ارسال شده',
                'delivered' => 'تحویل شده',
                'cancelled' => 'لغو شده',
                'returned' => 'مرجوعی',
            ],

            'lowStockVariants' => $lowStockVariants,

            'recentOrders' => Order::query()
                ->with('user')
                ->latest('placed_at')
                ->latest('id')
                ->limit(7)
                ->get(),

            'topProducts' => Product::query()
                ->with([
                    'category:id,name',
                ])
                ->withSum('orderItems', 'quantity')
                ->orderByDesc('order_items_sum_quantity')
                ->orderBy('id')
                ->limit(6)
                ->get(),
        ]);
    }
}
