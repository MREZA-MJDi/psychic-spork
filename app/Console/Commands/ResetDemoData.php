<?php

namespace App\Console\Commands;

use Database\Seeders\DemoDataSeeder;
use App\Models\Order;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResetDemoData extends Command
{
    protected $signature = 'demo:reset {--no-seed : Only remove the demo records}';
    protected $description = 'Reset Janan demo customers, orders, payments, accounting and wholesale demo data';

    public function handle(): int
    {
        $emails = [
            'demo-customer@janan.local',
            'wholesale@janan.local',
            'cheque@janan.local',
        ];

        $userIds = User::query()->whereIn('email', $emails)->pluck('id');

        DB::transaction(function () use ($userIds): void {
            $orderIds = Order::query()->whereIn('user_id', $userIds)->pluck('id');

            if ($orderIds->isNotEmpty()) {
                DB::table('cheque_payments')
                    ->whereIn('order_id', $orderIds)
                    ->delete();
                DB::table('payments')
                    ->whereIn('order_id', $orderIds)
                    ->delete();

                DB::table('financial_transactions')
                    ->where('reference_type', Order::class)
                    ->whereIn('reference_id', $orderIds)
                    ->delete();

                DB::table('journal_entries')
                    ->where('reference_type', Order::class)
                    ->whereIn('reference_id', $orderIds)
                    ->delete();

                DB::table('order_items')
                    ->whereIn('order_id', $orderIds)
                    ->delete();

                DB::table('orders')
                    ->whereIn('id', $orderIds)
                    ->delete();
            }

            DB::table('cheque_permissions')->whereIn('user_id', $userIds)->delete();
            DB::table('wholesale_profiles')->whereIn('user_id', $userIds)->delete();
            DB::table('addresses')->whereIn('user_id', $userIds)->delete();

            DB::table('wholesale_pack_items')
                ->whereIn(
                    'wholesale_pack_id',
                    DB::table('wholesale_packs')
                        ->whereIn('slug', ['demo-wholesale-pack-12', 'demo-wholesale-pack-6'])
                        ->pluck('id')
                )
                ->delete();

            DB::table('wholesale_packs')
                ->whereIn('slug', ['demo-wholesale-pack-12', 'demo-wholesale-pack-6'])
                ->delete();

            User::query()->whereIn('id', $userIds)->delete();
        });

        $this->info('Janan demo records removed.');

        if ($this->option('no-seed')) {
            return self::SUCCESS;
        }

        $this->call('db:seed', [
            '--class' => DemoDataSeeder::class,
            '--force' => true,
        ]);

        return self::SUCCESS;
    }
}
