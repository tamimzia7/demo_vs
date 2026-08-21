<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visit_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('promoted_to_vin')->nullable();
            $table->timestamps();

            $table->index(['visit_id', 'tenant_id']);
            $table->index('promoted_to_vin');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visit_participants');
    }
};
