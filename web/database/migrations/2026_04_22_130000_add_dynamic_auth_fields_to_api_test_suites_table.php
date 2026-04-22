<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('endpoints', function (Blueprint $table) {
            $table->timestamp('bearer_token_expires_at')->nullable()->after('bearer_token');
            $table->string('auth_login_path')->nullable()->after('bearer_token_expires_at');
            $table->string('auth_login_method', 10)->default('POST')->after('auth_login_path');
            $table->json('auth_payload')->nullable()->after('auth_login_method');
            $table->string('auth_token_path')->default('token')->after('auth_payload');
            $table->string('auth_validate_path')->nullable()->after('auth_token_path');
            $table->string('auth_validate_method', 10)->default('GET')->after('auth_validate_path');
            $table->unsignedSmallInteger('auth_validate_status')->default(200)->after('auth_validate_method');
        });
    }

    public function down(): void
    {
        Schema::table('endpoints', function (Blueprint $table) {
            $table->dropColumn([
                'bearer_token_expires_at',
                'auth_login_path',
                'auth_login_method',
                'auth_payload',
                'auth_token_path',
                'auth_validate_path',
                'auth_validate_method',
                'auth_validate_status',
            ]);
        });
    }
};
