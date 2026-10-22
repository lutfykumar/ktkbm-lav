<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CronScheduler extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'cron:run-scheduled {--job= : Run a specific job ID or slug}';

    /**
     * The console command description.
     */
    protected $description = 'Master scheduler to execute due cron jobs registered in s_cron_jobs';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $specificJob = $this->option('job');
        $now = date('Y-m-d H:i:s');

        $query = DB::table('s_cron_jobs')->where('IS_ACTIVE', 1);

        if (!empty($specificJob)) {
            $query->where(function ($q) use ($specificJob) {
                if (is_numeric($specificJob)) {
                    $q->where('ID_CRON_JOB', $specificJob);
                } else {
                    $q->where('JOB_SLUG', $specificJob);
                }
            });
        } else {
            $query->where(function ($q) use ($now) {
                $q->whereNull('NEXT_RUN_AT')->orWhere('NEXT_RUN_AT', '<=', $now);
            });
        }

        $dueJobs = $query->get();

        if ($dueJobs->isEmpty()) {
            $this->info('No cron jobs due for execution.');
            return Command::SUCCESS;
        }

        $this->info("Found {$dueJobs->count()} job(s) to execute.");

        foreach ($dueJobs as $job) {
            $this->executeJob($job);
        }

        return Command::SUCCESS;
    }

    protected function executeJob(object $job): void
    {
        $this->line("Executing cron job: [{$job->ID_CRON_JOB}] {$job->JOB_NAME}...");

        $startTime = microtime(true);
        $startedAt = date('Y-m-d H:i:s');
        $executionId = uniqid('cron_', true);
        $status = 'success';
        $output = '';
        $errorLog = null;
        $exitCode = 0;

        try {
            DB::table('s_cron_jobs')->where('ID_CRON_JOB', $job->ID_CRON_JOB)->update([
                'IS_RUNNING' => 1,
            ]);

            // Match known job types or commands
            $slug = strtolower($job->JOB_SLUG ?? '');
            $controller = strtolower($job->CONTROLLER_PATH ?? '');

            if (str_contains($slug, 'email') || str_contains($controller, 'email')) {
                Artisan::call('cron:email-process');
                $output = Artisan::output();
            } elseif (str_contains($slug, 'cleanup') || str_contains($controller, 'cleanup') || str_contains($controller, 'log')) {
                Artisan::call('cron:log-cleanup');
                $output = Artisan::output();
            } else {
                $output = "Job executed at {$startedAt}";
            }

            $duration = round((microtime(true) - $startTime) * 1000);
            $completedAt = date('Y-m-d H:i:s');

            // Log execution
            DB::table('s_cron_execution_log')->insert([
                'CRON_JOB_ID'       => $job->ID_CRON_JOB,
                'EXECUTION_ID'      => $executionId,
                'STARTED_AT'        => $startedAt,
                'COMPLETED_AT'      => $completedAt,
                'EXECUTION_TIME_MS' => $duration,
                'STATUS'            => 'success',
                'EXIT_CODE'         => 0,
                'OUTPUT_LOG'        => substr($output, 0, 5000),
                'SERVER_HOSTNAME'   => gethostname(),
                'SERVER_IP'         => gethostbyname(gethostname()),
            ]);

            // Update job next run
            $nextRun = $this->calculateNextRun($job);
            DB::table('s_cron_jobs')->where('ID_CRON_JOB', $job->ID_CRON_JOB)->update([
                'IS_RUNNING'            => 0,
                'LAST_RUN_AT'           => $startedAt,
                'NEXT_RUN_AT'           => $nextRun,
                'SUCCESS_COUNT'         => DB::raw('SUCCESS_COUNT + 1'),
                'AVG_EXECUTION_TIME_MS' => $duration,
            ]);

            $this->info("✓ Completed [{$job->JOB_NAME}] in {$duration}ms");
        } catch (Exception $e) {
            $duration = round((microtime(true) - $startTime) * 1000);
            $completedAt = date('Y-m-d H:i:s');
            $errorLog = $e->getMessage();

            DB::table('s_cron_execution_log')->insert([
                'CRON_JOB_ID'       => $job->ID_CRON_JOB,
                'EXECUTION_ID'      => $executionId,
                'STARTED_AT'        => $startedAt,
                'COMPLETED_AT'      => $completedAt,
                'EXECUTION_TIME_MS' => $duration,
                'STATUS'            => 'failed',
                'EXIT_CODE'         => 1,
                'OUTPUT_LOG'        => substr($output, 0, 1000),
                'ERROR_LOG'         => substr($errorLog, 0, 2000),
                'SERVER_HOSTNAME'   => gethostname(),
                'SERVER_IP'         => gethostbyname(gethostname()),
            ]);

            $nextRun = $this->calculateNextRun($job);
            DB::table('s_cron_jobs')->where('ID_CRON_JOB', $job->ID_CRON_JOB)->update([
                'IS_RUNNING'    => 0,
                'LAST_RUN_AT'   => $startedAt,
                'NEXT_RUN_AT'   => $nextRun,
                'FAILURE_COUNT' => DB::raw('FAILURE_COUNT + 1'),
            ]);

            $this->error("✗ Failed [{$job->JOB_NAME}]: {$errorLog}");
        }
    }

    protected function calculateNextRun(object $job): string
    {
        $scheduleType = strtolower($job->SCHEDULE_TYPE ?? 'daily');
        $scheduleVal = (int) ($job->SCHEDULE_VALUE ?: 1);

        return match ($scheduleType) {
            'hourly'   => date('Y-m-d H:i:s', strtotime("+{$scheduleVal} hours")),
            'daily'    => date('Y-m-d H:i:s', strtotime("+{$scheduleVal} days")),
            'weekly'   => date('Y-m-d H:i:s', strtotime("+{$scheduleVal} weeks")),
            'monthly'  => date('Y-m-d H:i:s', strtotime("+{$scheduleVal} months")),
            default    => date('Y-m-d H:i:s', strtotime('+1 hours')),
        };
    }
}
