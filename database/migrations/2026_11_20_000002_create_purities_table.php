<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purities', function (Blueprint $table) {
            $table->id();
            $table->enum('metal', ['gold', 'silver']);
            $table->string('karat', 30);                          // 24K, 22K, 21K, 18K, Traditional
            $table->string('label');                              // "22K Gold"
            $table->decimal('purity_percent', 5, 2)->nullable();  // 91.60
            $table->string('tagline')->nullable();                // "Most popular"
            $table->text('description')->nullable();              // karat guide text
            $table->boolean('is_featured')->default(false);       // hero metric cards
            $table->boolean('show_in_table')->default(true);      // rate tables
            $table->boolean('show_in_calculator')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['metal', 'karat']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purities');
    }
};
