<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Empty duplicate users table (system uses s_users from Ion Auth)
        Schema::dropIfExists('users');

        // 2. Legacy CI3 Ion Auth login attempts (Laravel uses Cache RateLimiter)
        Schema::dropIfExists('login_attempts');

        // 3. Legacy CI3 rules table (jamsostek, thr, pesangon, seragam, pph)
        Schema::dropIfExists('s_aturan');

        // 4. Legacy CI3 cron alerts table (handled by execution logs and Laravel logs)
        Schema::dropIfExists('s_cron_alerts');

        // 5. Legacy CI3 performance logs
        Schema::dropIfExists('s_log_performance');

        // 6. Legacy CI3 security logs
        Schema::dropIfExists('s_log_security');

        // 7. Obsolete settings groups pivot (settings grouped directly via group string column)
        Schema::dropIfExists('app_settings_groups');

        // 8. Legacy s_appconfig table (fully replaced by app_settings)
        Schema::dropIfExists('s_appconfig');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse operation as these are legacy/unused tables
    }
};
