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
        // 1. Stock Ledger (Kartu Stok)
        Schema::create('stock_ledgers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('cascade');
            $table->string('transaction_type', 50); // in, out, transfer_in, transfer_out, adjustment, return_in, return_out
            $table->string('reference_type', 50); // stock_in, stock_out, stock_transfer, stock_opname, stock_return
            $table->unsignedBigInteger('reference_id');
            $table->foreignId('unit_id')->nullable()->constrained('units');
            $table->decimal('qty_in', 15, 2)->default(0);
            $table->decimal('qty_out', 15, 2)->default(0);
            $table->decimal('balance', 15, 2);
            $table->decimal('price', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->date('transaction_date');
            $table->timestamp('created_at')->nullable();
        });

        // 2. Notification Settings
        Schema::create('notification_settings', function (Blueprint $table) {
            $table->id();
            $table->enum('notification_type', ['low_stock', 'out_of_stock']);
            $table->enum('channel', ['email', 'in_app']);
            $table->enum('recipient_type', ['user', 'role']);
            $table->unsignedBigInteger('recipient_id');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3. Notification Logs
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('cascade');
            $table->enum('notification_type', ['low_stock', 'out_of_stock']);
            $table->enum('channel', ['email', 'in_app']);
            $table->unsignedBigInteger('recipient_id');
            $table->timestamp('sent_at')->nullable();
        });

        // 4. Activity Logs (Audit Trail)
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('module', 50);
            $table->string('action', 50);
            $table->text('description')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('notification_logs');
        Schema::dropIfExists('notification_settings');
        Schema::dropIfExists('stock_ledgers');
    }
};
