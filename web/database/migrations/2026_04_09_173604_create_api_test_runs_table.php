<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('api_test_runs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->integer('total_tests')->default(0);
            $table->integer('passed')->default(0);
            $table->integer('failed')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_test_runs');
    }
};
