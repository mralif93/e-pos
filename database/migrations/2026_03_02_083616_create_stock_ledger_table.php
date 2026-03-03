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
        Schema::create('stock_ledger', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $blueprint->foreignId('outlet_id')->nullable()->constrained('outlets')->onDelete('set null');
            $blueprint->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            
            // Quantity changed (+ for in, - for out)
            $blueprint->decimal('quantity', 12, 4); 
            
            // Valuation data
            $blueprint->decimal('unit_cost', 12, 4)->default(0); // Cost at the time of movement
            $blueprint->decimal('unit_price', 12, 4)->nullable(); // Price at the time of movement (optional for 'out')
            
            // Context
            $blueprint->string('type'); // e.g., 'SALE', 'PURCHASE', 'ADJUSTMENT', 'TRANSFER', 'RETURN'
            $blueprint->string('reference_type')->nullable(); // Model name e.g., 'Sale', 'InventoryTransfer'
            $blueprint->unsignedBigInteger('reference_id')->nullable();
            $blueprint->text('notes')->nullable();
            
            $blueprint->timestamps();

            // Indexes for valuation reports
            $blueprint->index(['product_id', 'outlet_id', 'created_at']);
            $blueprint->index(['reference_type', 'reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_ledger');
    }
};
