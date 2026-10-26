<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('s_users', function (Blueprint $table) {
            $table->rememberToken()->after('phone');
            $table->dropColumn(['remember_selector', 'remember_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('s_users', function (Blueprint $table) {
            $table->dropRememberToken();
            $table->string('remember_selector', 255)->nullable();
            $table->string('remember_code', 255)->nullable();
        });
    }
};
