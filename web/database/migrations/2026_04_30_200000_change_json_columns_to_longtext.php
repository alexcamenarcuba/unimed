<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL JSON columns sort keys alphabetically, destroying user-defined field order.
        // Changing to longtext preserves the exact JSON string as stored.

        Schema::table('api_test_cases', function (Blueprint $table) {
            $table->longText('request_payload')->nullable()->change();
            $table->longText('variable_overrides')->nullable()->change();
            $table->longText('expected_response')->nullable()->change();
        });

        Schema::table('endpoints', function (Blueprint $table) {
            $table->longText('variables')->nullable()->change();
        });

        Schema::table('api_test_results', function (Blueprint $table) {
            $table->longText('request_payload')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('api_test_cases', function (Blueprint $table) {
            $table->json('request_payload')->nullable()->change();
            $table->json('variable_overrides')->nullable()->change();
            $table->json('expected_response')->nullable()->change();
        });

        Schema::table('endpoints', function (Blueprint $table) {
            $table->json('variables')->nullable()->change();
        });

        Schema::table('api_test_results', function (Blueprint $table) {
            $table->json('request_payload')->nullable()->change();
        });
    }
};
