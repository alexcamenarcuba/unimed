<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('api_test_results', function (Blueprint $table) {
            $table->uuid('environment_id')->nullable()->after('test_case_id');
            $table->foreign('environment_id')->references('id')->on('api_test_environments')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('api_test_results', function (Blueprint $table) {
            $table->dropForeign(['environment_id']);
            $table->dropColumn('environment_id');
        });
    }
};
