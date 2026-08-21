<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timeline_events', function (Blueprint $table) {
            $table->id();
            $table->string('evn')->unique();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('visitor_vin');
            $table->enum('type', ['user', 'system']);
            $table->string('source');
            $table->string('summary');
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();

            $table->index(['visitor_vin', 'created_at']);
            $table->index(['tenant_id', 'visitor_vin']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timeline_events');
    }
};
