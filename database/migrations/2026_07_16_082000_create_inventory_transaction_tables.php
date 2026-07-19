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
        // 1. Stock In (Barang Masuk)
        Schema::create('stock_ins', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_no', 50)->unique();
            $table->date('transaction_date');
            $table->foreignId('supplier_id')->constrained('suppliers');
            $table->foreignId('warehouse_id')->constrained('warehouses');
            $table->string('reference_no', 50)->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['draft', 'completed'])->default('draft');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        Schema::create('stock_in_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_in_id')->constrained('stock_ins')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('unit_id')->constrained('units');
            $table->decimal('qty', 15, 2);
            $table->decimal('qty_base_unit', 15, 2);
            $table->decimal('price', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->timestamps();
        });

        // 2. Stock Out (Barang Keluar)
        Schema::create('stock_outs', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_no', 50)->unique();
            $table->date('transaction_date');
            $table->string('recipient', 150)->nullable();
            $table->foreignId('warehouse_id')->constrained('warehouses');
            $table->string('reference_no', 50)->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['draft', 'completed'])->default('draft');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        Schema::create('stock_out_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_out_id')->constrained('stock_outs')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('unit_id')->constrained('units');
            $table->decimal('qty', 15, 2);
            $table->decimal('qty_base_unit', 15, 2);
            $table->decimal('price', 15, 2)->nullable();
            $table->decimal('subtotal', 15, 2)->nullable();
            $table->timestamps();
        });

        // 3. Stock Transfer
        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_no', 50)->unique();
            $table->date('transaction_date');
            $table->foreignId('from_warehouse_id')->constrained('warehouses');
            $table->foreignId('to_warehouse_id')->constrained('warehouses');
            $table->string('reference_no', 100)->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['draft', 'completed'])->default('draft');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        Schema::create('stock_transfer_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_transfer_id')->constrained('stock_transfers')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('unit_id')->constrained('units');
            $table->decimal('qty', 15, 2);
            $table->decimal('qty_base_unit', 15, 2);
            $table->decimal('price', 15, 2)->nullable();
            $table->timestamps();
        });

        // 4. Stock Opname
        Schema::create('stock_opnames', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_no', 50)->unique();
            $table->date('opname_date');
            $table->foreignId('warehouse_id')->constrained('warehouses');
            $table->text('notes')->nullable();
            $table->enum('status', ['draft', 'completed'])->default('draft');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        Schema::create('stock_opname_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_opname_id')->constrained('stock_opnames')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products');
            $table->decimal('system_qty', 15, 2);
            $table->decimal('physical_qty', 15, 2);
            $table->decimal('difference', 15, 2);
            $table->string('notes', 255)->nullable();
            $table->timestamps();
        });

        // 5. Stock Returns
        Schema::create('stock_returns', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_no', 50)->unique();
            $table->date('return_date');
            $table->enum('return_type', ['return_in', 'return_out']);
            $table->enum('reference_type', ['stock_in', 'stock_out']);
            $table->unsignedBigInteger('reference_id');
            $table->foreignId('warehouse_id')->constrained('warehouses');
            $table->text('reason')->nullable();
            $table->enum('status', ['draft', 'completed'])->default('draft');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        Schema::create('stock_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_return_id')->constrained('stock_returns')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('unit_id')->constrained('units');
            $table->decimal('qty', 15, 2);
            $table->decimal('qty_base_unit', 15, 2);
            $table->string('notes', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_return_items');
        Schema::dropIfExists('stock_returns');
        Schema::dropIfExists('stock_opname_items');
        Schema::dropIfExists('stock_opnames');
        Schema::dropIfExists('stock_transfer_items');
        Schema::dropIfExists('stock_transfers');
        Schema::dropIfExists('stock_out_items');
        Schema::dropIfExists('stock_outs');
        Schema::dropIfExists('stock_in_items');
        Schema::dropIfExists('stock_ins');
    }
};
