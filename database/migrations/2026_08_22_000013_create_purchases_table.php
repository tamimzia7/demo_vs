<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('visitor_vin');
            $table->foreignId('offering_id')->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->timestamp('purchased_at');
            $table->timestamps();

            $table->index(['visitor_vin', 'purchased_at']);
            $table->index(['tenant_id', 'visitor_vin']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
