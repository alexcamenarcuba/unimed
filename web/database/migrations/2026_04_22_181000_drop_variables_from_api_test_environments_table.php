<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('api_test_environments', 'variables')) {
            Schema::table('api_test_environments', function (Blueprint $table) {
                $table->dropColumn('variables');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasColumn('api_test_environments', 'variables')) {
            Schema::table('api_test_environments', function (Blueprint $table) {
                $table->json('variables')->nullable()->after('auth_validate_status');
            });
        }
    }
};
