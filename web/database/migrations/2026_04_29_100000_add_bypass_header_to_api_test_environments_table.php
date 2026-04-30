<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('api_test_environments', function (Blueprint $table) {
            $table->string('bypass_header_name', 100)->nullable()->after('auth_validate_status');
            $table->string('bypass_header_value', 500)->nullable()->after('bypass_header_name');
        });
    }

    public function down(): void
    {
        Schema::table('api_test_environments', function (Blueprint $table) {
            $table->dropColumn(['bypass_header_name', 'bypass_header_value']);
        });
    }
};
