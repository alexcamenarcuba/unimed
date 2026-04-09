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
        Schema::create('api_test_results', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('run_id');
            $table->uuid('test_case_id');
            $table->foreign('run_id')->references('id')->on('api_test_runs')->cascadeOnDelete();
            $table->foreign('test_case_id')->references('id')->on('api_test_cases')->cascadeOnDelete();
            $table->boolean('passed');
            $table->integer('status_received');
            $table->json('response_body')->nullable();
            $table->json('errors')->nullable();
            $table->decimal('response_time_ms', 8, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_test_results');
    }
};
