<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('price_histories', function (Blueprint $table) {
            // manual = set by an admin, auto = fetched from the live source, seed = demo data
            $table->string('source', 20)->default('manual')->after('note');
            $table->decimal('sell_price_per_gram', 12, 2)->nullable()->after('price_per_gram');
        });
        DB::table('price_histories')->whereNull('updated_by')->update(['source' => 'seed']);

        Schema::create('rate_fetch_logs', function (Blueprint $table) {
            $table->id();
            $table->string('status', 20);              // success / unchanged / skipped / failed
            $table->string('trigger', 20);             // schedule / visitor / admin / cli
            $table->string('message', 500)->nullable();
            $table->json('changes')->nullable();       // [{label, old, new}]
            $table->unsignedInteger('duration_ms')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rate_fetch_logs');
        Schema::table('price_histories', function (Blueprint $table) {
            $table->dropColumn(['source', 'sell_price_per_gram']);
        });
    }
};
