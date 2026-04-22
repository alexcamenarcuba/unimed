<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('api_test_environments', function (Blueprint $table) {
            $table->json('variables')->nullable()->after('auth_validate_status');
        });
    }

    public function down(): void
    {
        Schema::table('api_test_environments', function (Blueprint $table) {
            $table->dropColumn('variables');
        });
    }
};
