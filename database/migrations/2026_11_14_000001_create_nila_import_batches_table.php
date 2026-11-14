<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nila_import_batches', function (Blueprint $table) {
            $table->id();
            $table->string('original_name', 255);
            $table->string('disk', 40)->default('local');
            $table->string('file_path', 500);
            $table->string('status', 30)->default('preview_ready')->index();
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('valid_rows')->default(0);
            $table->unsignedInteger('invalid_rows')->default(0);
            $table->unsignedInteger('product_groups')->default(0);
            $table->unsignedInteger('next_group_offset')->default(0);
            $table->unsignedInteger('imported_groups')->default(0);
            $table->unsignedInteger('failed_groups')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['created_by', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nila_import_batches');
    }
};
