<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('relationships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('visitor_vin');
            $table->foreignId('marketer_id')->constrained('users');
            $table->enum('status', [
                'unassigned',
                'assigned',
                'transfer_requested',
                'transferred',
                'rejected',
            ])->default('unassigned');
            $table->foreignId('transferred_from_id')->nullable()->constrained('users');
            $table->timestamps();

            $table->index(['tenant_id', 'visitor_vin']);
            $table->index(['tenant_id', 'marketer_id']);
            $table->index('visitor_vin');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('relationships');
    }
};
