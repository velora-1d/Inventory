<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('stock_transfer_items', 'price')) {
            Schema::table('stock_transfer_items', function (Blueprint $table) {
                $table->decimal('price', 15, 2)->nullable()->after('qty_base_unit');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('stock_transfer_items', 'price')) {
            Schema::table('stock_transfer_items', function (Blueprint $table) {
                $table->dropColumn('price');
            });
        }
    }
};
