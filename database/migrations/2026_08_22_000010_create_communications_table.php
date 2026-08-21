<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('communications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('visitor_vin');
            $table->enum('channel', ['sms', 'email', 'notice', 'call', 'meeting']);
            $table->text('content')->nullable();
            $table->foreignId('notice_id')->nullable();
            $table->timestamp('sent_at');
            $table->timestamps();

            $table->index(['visitor_vin', 'created_at']);
            $table->index(['tenant_id', 'visitor_vin']);
            $table->index('channel');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('communications');
    }
};
