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
            $table->uuid('visitor_key')->unique();           // kept in a long-lived cookie
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('browser', 50)->nullable();
            $table->string('platform', 50)->nullable();
            $table->string('device_type', 20)->nullable();   // desktop / mobile / tablet / bot
            $table->boolean('is_bot')->default(false);
            $table->string('language', 20)->nullable();
            $table->string('first_referrer', 500)->nullable();
            $table->string('first_landing', 500)->nullable();
            $table->unsignedInteger('total_visits')->default(0);
            $table->timestamp('first_visit_at')->nullable();
            $table->timestamp('last_visit_at')->nullable();
            $table->timestamps();

            $table->index('last_visit_at');
            $table->index('device_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitors');
    }
};
