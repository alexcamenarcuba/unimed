<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_test_case_groups', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('suite_id');
            $table->string('name');
            $table->timestamps();

            $table->foreign('suite_id')->references('id')->on('api_test_suites')->cascadeOnDelete();
            $table->unique(['suite_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_test_case_groups');
    }
};