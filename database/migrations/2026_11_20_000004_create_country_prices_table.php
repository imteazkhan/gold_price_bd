<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('country_prices', function (Blueprint $table) {
            $table->id();
            $table->string('country');
            $table->string('flag', 16)->nullable();
            $table->string('currency', 10);
            $table->string('karat', 30)->default('24K');
            $table->decimal('local_price', 12, 2);   // per gram, local currency
            $table->decimal('fx_to_bdt', 12, 4);     // 1 local unit = x BDT
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['karat', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('country_prices');
    }
};
