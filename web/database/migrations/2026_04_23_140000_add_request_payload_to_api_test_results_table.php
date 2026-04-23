<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('api_test_results', function (Blueprint $table) {
            $table->json('request_payload')->nullable()->after('status_received');
        });
    }

    public function down(): void
    {
        Schema::table('api_test_results', function (Blueprint $table) {
            $table->dropColumn('request_payload');
        });
    }
};
