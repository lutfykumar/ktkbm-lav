<?php

namespace Tests\Feature\Cron;

use Tests\TestCase;

class CronCommandsTest extends TestCase
{
    public function test_cron_log_cleanup_dry_run_command(): void
    {
        $this->artisan('cron:log-cleanup', ['--dry-run' => true])
            ->assertSuccessful();
    }

    public function test_cron_email_process_command(): void
    {
        $this->artisan('cron:email-process', ['--limit' => 5])
            ->assertSuccessful();
    }

    public function test_cron_run_scheduled_command(): void
    {
        $this->artisan('cron:run-scheduled')
            ->assertSuccessful();
    }
}
