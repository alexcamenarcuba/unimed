<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('api_test_runs', function (Blueprint $table) {
            $table->uuid('suite_id')->nullable()->after('id');
            $table->foreign('suite_id')->references('id')->on('api_test_suites')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('api_test_runs', function (Blueprint $table) {
            $table->dropForeign(['suite_id']);
            $table->dropColumn('suite_id');
        });
    }
};
