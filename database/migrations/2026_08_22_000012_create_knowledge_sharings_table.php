<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('knowledge_sharings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('knowledge_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('visitor_vin');
            $table->enum('status', ['granted', 'revoked'])->default('granted');
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            $table->index(['knowledge_item_id', 'visitor_vin']);
            $table->index(['tenant_id', 'visitor_vin']);
            $table->index('visitor_vin');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('knowledge_sharings');
    }
};
