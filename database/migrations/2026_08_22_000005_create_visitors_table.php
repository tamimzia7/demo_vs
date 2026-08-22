<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visitors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('vin');
            $table->string('name');
            $table->string('channel')->nullable();
            $table->json('contact')->nullable();
            $table->string('referrer_vin')->nullable();
            $table->enum('lifecycle_state', [
                'Interested',
                'Negotiating',
                'Purchased',
                'Repeat Customer',
                'VIP',
                'Archived',
            ])->default('Interested');
            $table->timestamp('archived_at')->nullable();
            $table->integer('event_count')->default(0);
            $table->timestamps();

            $table->unique(['tenant_id', 'vin']);
            $table->index(['tenant_id', 'lifecycle_state']);
            $table->index(['tenant_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitors');
    }
};
