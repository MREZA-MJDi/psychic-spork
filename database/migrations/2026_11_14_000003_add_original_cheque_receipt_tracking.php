<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cheque_payments', function (Blueprint $table): void {
            $table->timestamp('original_received_at')->nullable()->after('review_note');
            $table->foreignId('original_received_by')
                ->nullable()
                ->after('original_received_at')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('cheque_payments', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('original_received_by');
            $table->dropColumn('original_received_at');
        });
    }
};
