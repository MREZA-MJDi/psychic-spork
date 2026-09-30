<?php

use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Database\\Schema\\Blueprint;
use Illuminate\\Support\\Facades\\Schema;

return new class extends Migration
{
    private const INDEX_NAME = 'wholesale_profiles_status_minimums_index';

    public function up(): void
    {
        Schema::table('wholesale_profiles', function (Blueprint $table): void {
            $table->string('business_name', 160)->nullable()->after('user_id');
            $table->string('business_type', 120)->nullable()->after('business_name');
            $table->string('business_phone', 30)->nullable()->after('business_type');
            $table->text('business_address')->nullable()->after('business_phone');
            $table->decimal('minimum_order_amount', 14, 2)
                ->nullable()
                ->after('admin_note');
            $table->unsignedInteger('minimum_order_quantity')
                ->nullable()
                ->after('minimum_order_amount');

            $table->index(
                ['status', 'minimum_order_amount', 'minimum_order_quantity'],
                self::INDEX_NAME
            );
        });
    }

    public function down(): void
    {
        Schema::table('wholesale_profiles', function (Blueprint $table): void {
            $table->dropIndex(self::INDEX_NAME);

            $table->dropColumn([
                'business_name',
                'business_type',
                'business_phone',
                'business_address',
                'minimum_order_amount',
                'minimum_order_quantity',
            ]);
        });
    }
};
