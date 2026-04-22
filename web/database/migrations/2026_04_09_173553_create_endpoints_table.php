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
        Schema::create('endpoints', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('suite_id');
            $table->foreign('suite_id')
                ->references('id')
                ->on('api_test_suites')
                ->cascadeOnDelete();
            $table->string('name');
            $table->string('method', 10);
            $table->string('path');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('endpoints');
    }
};
