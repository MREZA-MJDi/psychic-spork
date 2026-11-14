<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cart_wholesale_packs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('cart_id')->constrained()->cascadeOnDelete();
            $table->foreignId('wholesale_pack_id')->constrained('wholesale_packs')->restrictOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->timestamps();

            $table->unique(['cart_id', 'wholesale_pack_id']);
        });

        Schema::create('order_wholesale_packs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('wholesale_pack_id')
                ->nullable()
                ->constrained('wholesale_packs')
                ->nullOnDelete();
            $table->string('pack_name', 180);
            $table->string('pack_slug', 180);
            $table->string('image_path', 500)->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_pack_price', 14, 2);
            $table->decimal('line_total', 14, 2);
            $table->json('items_snapshot');
            $table->timestamps();

            $table->index(['order_id', 'id']);
        });

        Schema::table('order_items', function (Blueprint $table): void {
            $table->foreignId('wholesale_pack_order_id')
                ->nullable()
                ->after('order_id')
                ->constrained('order_wholesale_packs')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('wholesale_pack_order_id');
        });

        Schema::dropIfExists('order_wholesale_packs');
        Schema::dropIfExists('cart_wholesale_packs');
    }
};
