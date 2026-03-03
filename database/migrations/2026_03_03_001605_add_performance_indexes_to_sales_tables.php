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
        Schema::table('sales', function (Blueprint $table) {
            $table->index(['outlet_id', 'created_at']);
            $table->index(['outlet_id', 'status']);
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->index(['sale_id', 'product_id']);
        });

        Schema::table('stock_ledger', function (Blueprint $table) {
            // Already added some, but let's ensure full coverage for common manager reports
            $table->index(['outlet_id', 'type', 'created_at'], 'idx_stock_report_base');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex(['outlet_id', 'created_at']);
            $table->dropIndex(['outlet_id', 'status']);
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropIndex(['sale_id', 'product_id']);
        });

        Schema::table('stock_ledger', function (Blueprint $table) {
            $table->dropIndex('idx_stock_report_base');
        });
    }
};
