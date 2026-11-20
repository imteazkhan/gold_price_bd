<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saved_estimates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visitor_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('purity_id')->nullable()->constrained()->nullOnDelete();
            $table->string('karat', 30);
            $table->decimal('weight_grams', 10, 3);
            $table->decimal('rate_per_gram', 12, 2);
            $table->decimal('making_percent', 5, 2);
            $table->decimal('vat_percent', 5, 2);
            $table->decimal('base_amount', 14, 2);
            $table->decimal('making_amount', 14, 2);
            $table->decimal('vat_amount', 14, 2);
            $table->decimal('total_amount', 14, 2);
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_estimates');
    }
};
