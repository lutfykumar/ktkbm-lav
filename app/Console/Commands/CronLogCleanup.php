<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CronLogCleanup extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'cron:log-cleanup {--dry-run : Perform a dry run without deleting}';

    /**
     * The console command description.
     */
    protected $description = 'Clean up old logs based on retention policies';

    /**
     * Execute the console command.
     */
    public function handle(\App\Services\AppSettingService $settingService): int
    {
        $isDryRun = $this->option('dry-run');
        $this->info('Starting log cleanup process...' . ($isDryRun ? ' [DRY RUN]' : ''));

        $apiRetention = (int) $settingService->get('api_log_retention', 90);
        $errorRetention = (int) $settingService->get('error_log_retention', 365);
        $activityRetention = (int) $settingService->get('activity_log_retention', 180);
        $emailRetention = (int) $settingService->get('email_log_retention', 180);

        $results = [];

        // 1. API request logs
        $apiDate = date('Y-m-d H:i:s', strtotime("-{$apiRetention} days"));
        $apiQuery = DB::table('s_log_api_request')->where('CREATED_AT', '<', $apiDate);
        $apiCount = $apiQuery->count();
        if (!$isDryRun && $apiCount > 0) {
            $apiQuery->delete();
        }
        $results[] = ['API Request Logs', ">{$apiRetention} days", $apiCount];

        // 2. Error logs (resolved only)
        $errorDate = date('Y-m-d H:i:s', strtotime("-{$errorRetention} days"));
        $errorQuery = DB::table('s_log_error')->where('LAST_OCCURRED_AT', '<', $errorDate)->where('IS_RESOLVED', 1);
        $errorCount = $errorQuery->count();
        if (!$isDryRun && $errorCount > 0) {
            $errorQuery->delete();
        }
        $results[] = ['Error Logs (Resolved)', ">{$errorRetention} days", $errorCount];

        // 3. User Activity logs
        $activityDate = date('Y-m-d H:i:s', strtotime("-{$activityRetention} days"));
        $activityQuery = DB::table('s_user_activity_logs')->where('created_at', '<', $activityDate);
        $activityCount = $activityQuery->count();
        if (!$isDryRun && $activityCount > 0) {
            $activityQuery->delete();
        }
        $results[] = ['User Activity Logs', ">{$activityRetention} days", $activityCount];

        // 4. Email logs
        $emailDate = date('Y-m-d H:i:s', strtotime("-{$emailRetention} days"));
        $emailQuery = DB::table('s_log_email')->where('CREATED_AT', '<', $emailDate);
        $emailCount = $emailQuery->count();
        if (!$isDryRun && $emailCount > 0) {
            $emailQuery->delete();
        }
        $results[] = ['Email Delivery Logs', ">{$emailRetention} days", $emailCount];

        // 5. Cron execution logs (keep last 500 per job)
        $cronDeleted = 0;
        $cronJobs = DB::table('s_cron_jobs')->select('ID_CRON_JOB')->get();
        foreach ($cronJobs as $job) {
            $keepIds = DB::table('s_cron_execution_log')
                ->where('CRON_JOB_ID', $job->ID_CRON_JOB)
                ->orderBy('ID_EXECUTION', 'DESC')
                ->limit(500)
                ->pluck('ID_EXECUTION');

            if ($keepIds->isNotEmpty()) {
                $query = DB::table('s_cron_execution_log')
                    ->where('CRON_JOB_ID', $job->ID_CRON_JOB)
                    ->whereNotIn('ID_EXECUTION', $keepIds);

                $cronDeleted += $query->count();
                if (!$isDryRun) {
                    $query->delete();
                }
            }
        }
        $results[] = ['Cron Execution Logs', 'Keep last 500/job', $cronDeleted];

        $this->table(['Log Type', 'Policy', 'Records ' . ($isDryRun ? 'Eligible' : 'Deleted')], $results);

        Log::info('Log cleanup completed', ['results' => $results, 'dry_run' => $isDryRun]);

        return Command::SUCCESS;
    }
}
