<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_test_environments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('suite_id');
            $table->foreign('suite_id')->references('id')->on('api_test_suites')->cascadeOnDelete();
            $table->string('name');
            $table->string('base_url');
            $table->boolean('is_active')->default(true);
            $table->boolean('requires_auth')->default(false);
            $table->text('bearer_token')->nullable();
            $table->timestamp('bearer_token_expires_at')->nullable();
            $table->string('auth_login_path')->nullable();
            $table->string('auth_login_method', 10)->default('POST');
            $table->json('auth_payload')->nullable();
            $table->string('auth_token_path')->default('token');
            $table->string('auth_validate_path')->nullable();
            $table->string('auth_validate_method', 10)->default('GET');
            $table->unsignedSmallInteger('auth_validate_status')->default(200);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_test_environments');
    }
};
