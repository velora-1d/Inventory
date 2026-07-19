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
        Schema::table('stock_return_items', function (Blueprint $table) {
            $table->decimal('price', 15, 2)->nullable()->after('qty_base_unit');
            $table->decimal('subtotal', 15, 2)->nullable()->after('price');
            $table->string('condition', 50)->default('good')->after('subtotal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_return_items', function (Blueprint $table) {
            $table->dropColumn(['price', 'subtotal', 'condition']);
        });
    }
};
