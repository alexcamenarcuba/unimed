<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('api_test_results', function (Blueprint $table) {
            $table->string('variant_name')->nullable()->after('environment_id');
        });
    }

    public function down(): void
    {
        Schema::table('api_test_results', function (Blueprint $table) {
            $table->dropColumn('variant_name');
        });
    }
};
