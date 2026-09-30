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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('customer_name')->nullable()->after('invoice_number');
            $table->enum('order_type', ['dine_in', 'takeaway'])->default('dine_in')->after('customer_name');
            $table->string('table_number')->nullable()->after('order_type');
            $table->enum('payment_method', ['cash', 'qris'])->default('cash')->after('table_number');
            $table->string('payment_provider')->nullable()->after('payment_method'); // Menampung GoPay, DANA, OVO, dll.
            $table->text('notes')->nullable()->after('change_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'customer_name', 
                'order_type', 
                'table_number', 
                'payment_method', 
                'payment_provider', 
                'notes'
            ]);
        });
    }
};