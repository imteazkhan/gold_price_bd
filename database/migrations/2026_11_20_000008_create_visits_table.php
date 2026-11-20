<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visitor_id')->constrained()->cascadeOnDelete();
            $table->string('session_id', 100)->nullable();
            $table->string('method', 10)->default('GET');
            $table->string('path', 500);
            $table->string('full_url', 1000)->nullable();
            $table->string('referrer', 500)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('device_type', 20)->nullable();
            $table->timestamp('visited_at')->useCurrent();

            $table->index('visited_at');
            $table->index('path');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};
