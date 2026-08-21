<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('visitor_vin');
            $table->string('category');
            $table->decimal('amount', 10, 2)->nullable();
            $table->date('expense_date');
            $table->timestamps();

            $table->index(['visitor_vin', 'expense_date']);
            $table->index(['tenant_id', 'visitor_vin']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
