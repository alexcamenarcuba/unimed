<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('endpoints', function (Blueprint $table) {
            $table->boolean('requires_auth')->default(false)->after('path');
            $table->text('bearer_token')->nullable()->after('requires_auth');
        });
    }

    public function down(): void
    {
        Schema::table('endpoints', function (Blueprint $table) {
            $table->dropColumn(['requires_auth', 'bearer_token']);
        });
    }
};
