<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cheque_permissions', function (Blueprint $table): void {
            $table->string('status', 20)
                ->default('none')
                ->after('enabled')
                ->index();

            $table->timestamp('requested_at')
                ->nullable()
                ->after('status');
        });

        DB::table('cheque_permissions')
            ->where('enabled', true)
            ->update(['status' => 'approved']);
    }

    public function down(): void
    {
        Schema::table('cheque_permissions', function (Blueprint $table): void {
            $table->dropIndex(['status']);
            $table->dropColumn(['status', 'requested_at']);
        });
    }
};
