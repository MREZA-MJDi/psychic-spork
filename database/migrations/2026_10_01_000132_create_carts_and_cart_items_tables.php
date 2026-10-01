<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->ensureCartsTable();
        $this->ensureCartItemsTable();
    }

    private function ensureCartsTable(): void
    {
        if (! Schema::hasTable('carts')) {
            Schema::create('carts', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('session_id')->nullable();
                $table->timestamp('last_activity_at')->nullable();
                $table->timestamps();
                $table->unique('user_id');
                $table->unique('session_id');
                $table->index('last_activity_at');
            });
            return;
        }

        Schema::table('carts', function (Blueprint $table): void {
            if (! Schema::hasColumn('carts', 'user_id')) {
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            }
            if (! Schema::hasColumn('carts', 'session_id')) {
                $table->string('session_id')->nullable();
            }
            if (! Schema::hasColumn('carts', 'last_activity_at')) {
                $table->timestamp('last_activity_at')->nullable();
            }
            if (! Schema::hasColumn('carts', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }
            if (! Schema::hasColumn('carts', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });

        $this->ensureIndex('carts', 'carts_user_id_unique', 'UNIQUE', ['user_id']);
        $this->ensureIndex('carts', 'carts_session_id_unique', 'UNIQUE', ['session_id']);
        $this->ensureIndex('carts', 'carts_last_activity_at_index', 'INDEX', ['last_activity_at']);
    }

    private function ensureCartItemsTable(): void
    {
        if (! Schema::hasTable('cart_items')) {
            Schema::create('cart_items', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('cart_id')->constrained()->cascadeOnDelete();
                $table->foreignId('product_variant_id')->constrained()->restrictOnDelete();
                $table->unsignedInteger('quantity')->default(1);
                $table->timestamps();
                $table->unique(['cart_id', 'product_variant_id']);
            });
            return;
        }

        Schema::table('cart_items', function (Blueprint $table): void {
            if (! Schema::hasColumn('cart_items', 'cart_id')) {
                $table->foreignId('cart_id')->constrained()->cascadeOnDelete();
            }
            if (! Schema::hasColumn('cart_items', 'product_variant_id')) {
                $table->foreignId('product_variant_id')->constrained()->restrictOnDelete();
            }
            if (! Schema::hasColumn('cart_items', 'quantity')) {
                $table->unsignedInteger('quantity')->default(1);
            }
            if (! Schema::hasColumn('cart_items', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }
            if (! Schema::hasColumn('cart_items', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });

        $this->ensureIndex(
            'cart_items',
            'cart_items_cart_id_product_variant_id_unique',
            'UNIQUE',
            ['cart_id', 'product_variant_id']
        );
    }

    private function ensureIndex(string $table, string $index, string $type, array $columns): void
    {
        $rows = DB::select('SHOW INDEX FROM ' . $table);
        $existing = [];

        foreach ($rows as $row) {
            $key = (string) ($row->Key_name ?? '');
            $seq = (int) ($row->Seq_in_index ?? 0);
            $column = (string) ($row->Column_name ?? '');

            if ($key !== '' && $seq > 0 && $column !== '') {
                $existing[$key][$seq] = $column;
            }
        }

        foreach ($existing as $key => $indexedColumns) {
            ksort($indexedColumns);

            if (array_values($indexedColumns) === $columns) {
                return;
            }
        }

        $columnSql = implode(', ', $columns);
        $keyword = $type === 'UNIQUE' ? 'UNIQUE' : '';

        DB::statement(
            'ALTER TABLE ' . $table . ' ADD ' . $keyword . ' INDEX ' . $index . ' (' . $columnSql . ')'
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');
    }
};
