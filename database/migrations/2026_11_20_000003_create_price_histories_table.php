<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('price_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purity_id')->constrained()->cascadeOnDelete();
            $table->decimal('price_per_gram', 12, 2);
            $table->date('recorded_on');
            $table->string('note')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['purity_id', 'recorded_on']);
            $table->index('recorded_on');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('price_histories');
    }
};
