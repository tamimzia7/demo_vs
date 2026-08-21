<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('visitor_vin');
            $table->date('visit_date');
            $table->string('context')->nullable();
            $table->string('outcome')->nullable();
            $table->timestamps();

            $table->index(['visitor_vin', 'created_at']);
            $table->index(['tenant_id', 'visitor_vin']);
            $table->index('visit_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};
