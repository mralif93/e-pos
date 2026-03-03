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
        Schema::create('audit_logs', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $blueprint->foreignId('outlet_id')->nullable()->constrained('outlets')->onDelete('set null');
            $blueprint->string('action'); // e.g., 'VOID_SALE', 'PIN_VERIFY_FAILED', 'PRICE_OVERRIDE'
            $blueprint->text('description')->nullable();
            $blueprint->json('metadata')->nullable(); // For details like old values, request data, etc.
            $blueprint->string('ip_address')->nullable();
            $blueprint->string('user_agent')->nullable();
            $blueprint->timestamps();
            
            // Indexes for fast auditing
            $blueprint->index('action');
            $blueprint->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
