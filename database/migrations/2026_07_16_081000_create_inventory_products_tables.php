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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku', 50)->unique();
            $table->string('name', 150);
            $table->foreignId('category_id')->constrained('categories');
            $table->foreignId('base_unit_id')->constrained('units');
            $table->foreignId('purchase_unit_id')->nullable()->constrained('units');
            $table->foreignId('sale_unit_id')->nullable()->constrained('units');
            $table->decimal('purchase_price', 15, 2)->default(0);
            $table->decimal('sale_price', 15, 2)->default(0);
            $table->decimal('avg_price', 15, 2)->default(0);
            $table->decimal('min_stock', 15, 2)->default(0);
            $table->foreignId('default_warehouse_id')->nullable()->constrained('warehouses');
            $table->text('description')->nullable();
            $table->string('photo', 255)->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('product_unit_conversions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('from_unit_id')->constrained('units');
            $table->foreignId('to_unit_id')->constrained('units');
            $table->decimal('conversion_value', 15, 4);
            $table->timestamps();
        });

        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('cascade');
            $table->decimal('qty', 15, 2)->default(0);
            $table->timestamps();

            $table->unique(['product_id', 'warehouse_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stocks');
        Schema::dropIfExists('product_unit_conversions');
        Schema::dropIfExists('products');
    }
};
