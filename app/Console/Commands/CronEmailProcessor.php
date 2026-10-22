<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CronEmailProcessor extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'cron:email-process {--limit=50 : Maximum number of emails to process}';

    /**
     * The console command description.
     */
    protected $description = 'Process queued emails from s_email_queue and log results to s_log_email';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $limit = (int) $this->option('limit') ?: 50;
        $this->info("Starting email queue processor (limit: {$limit})...");

        // 1. Reset stuck processing items (> 15 minutes)
        $stuckCutoff = date('Y-m-d H:i:s', strtotime('-15 minutes'));
        $resetCount = DB::table('s_email_queue')
            ->where('STATUS', 'processing')
            ->where('UPDATED_AT', '<', $stuckCutoff)
            ->update([
                'STATUS'     => 'pending',
                'UPDATED_AT' => date('Y-m-d H:i:s'),
            ]);

        if ($resetCount > 0) {
            $this->warn("Reset {$resetCount} stuck email(s) back to pending.");
        }

        // 2. Fetch pending emails
        $now = date('Y-m-d H:i:s');
        $pending = DB::table('s_email_queue')
            ->where('STATUS', 'pending')
            ->where(function ($q) use ($now) {
                $q->whereNull('SCHEDULED_AT')->orWhere('SCHEDULED_AT', '<=', $now);
            })
            ->where('ATTEMPTS', '<', 3)
            ->orderBy('PRIORITY', 'DESC')
            ->orderBy('ID_QUEUE', 'ASC')
            ->limit($limit)
            ->get();

        if ($pending->isEmpty()) {
            $this->info('No pending emails in queue.');
            return Command::SUCCESS;
        }

        $sentCount = 0;
        $failedCount = 0;

        foreach ($pending as $queue) {
            $this->line("Processing email #{$queue->ID_QUEUE} to {$queue->EMAIL_TO}...");

            DB::table('s_email_queue')->where('ID_QUEUE', $queue->ID_QUEUE)->update([
                'STATUS'     => 'processing',
                'ATTEMPTS'   => DB::raw('ATTEMPTS + 1'),
                'UPDATED_AT' => date('Y-m-d H:i:s'),
            ]);

            $startTime = microtime(true);

            try {
                // Send raw email or html
                $to = $queue->EMAIL_TO;
                $subject = $queue->SUBJECT;
                $body = $queue->EMAIL_BODY ?: '';

                Mail::html($body, function ($message) use ($to, $subject, $queue) {
                    $message->to($to)->subject($subject);
                    if (!empty($queue->CC)) {
                        $message->cc(explode(',', $queue->CC));
                    }
                    if (!empty($queue->BCC)) {
                        $message->bcc(explode(',', $queue->BCC));
                    }
                });

                $duration = round((microtime(true) - $startTime) * 1000);

                // Insert into s_log_email
                $logId = DB::table('s_log_email')->insertGetId([
                    'SUBJECT'          => $subject,
                    'EMAIL_FROM'       => $queue->EMAIL_FROM ?: config('mail.from.address'),
                    'EMAIL_FROM_NAME'  => $queue->EMAIL_FROM_NAME ?: config('mail.from.name'),
                    'EMAIL_TO'         => $to,
                    'CC'               => $queue->CC,
                    'BCC'              => $queue->BCC,
                    'EMAIL_BODY'       => $body,
                    'EMAIL_CATEGORY'   => $queue->EMAIL_CATEGORY ?: 'general',
                    'TEMPLATE_USED'    => $queue->TEMPLATE_SLUG ?: $queue->TEMPLATE_NAME,
                    'RESPONSE_TIME'    => $duration,
                    'STATUS'           => 'sent',
                    'CREATED_AT'       => date('Y-m-d H:i:s'),
                    'UPDATED_AT'       => date('Y-m-d H:i:s'),
                ]);

                // Update queue
                DB::table('s_email_queue')->where('ID_QUEUE', $queue->ID_QUEUE)->update([
                    'STATUS'     => 'sent',
                    'SENT_AT'    => date('Y-m-d H:i:s'),
                    'LOG_ID'     => $logId,
                    'UPDATED_AT' => date('Y-m-d H:i:s'),
                ]);

                $sentCount++;
                $this->info("✓ Sent email #{$queue->ID_QUEUE} ({$duration}ms)");
            } catch (Exception $e) {
                $duration = round((microtime(true) - $startTime) * 1000);
                $errorMsg = $e->getMessage();
                Log::error("CronEmailProcessor failed on #{$queue->ID_QUEUE}: {$errorMsg}");

                $newAttempts = $queue->ATTEMPTS + 1;
                $newStatus = ($newAttempts >= ($queue->MAX_ATTEMPTS ?: 3)) ? 'failed' : 'pending';

                DB::table('s_log_email')->insert([
                    'SUBJECT'         => $queue->SUBJECT,
                    'EMAIL_FROM'      => $queue->EMAIL_FROM ?: config('mail.from.address'),
                    'EMAIL_FROM_NAME' => $queue->EMAIL_FROM_NAME ?: config('mail.from.name'),
                    'EMAIL_TO'        => $queue->EMAIL_TO,
                    'EMAIL_BODY'      => $queue->EMAIL_BODY ?: '',
                    'RESPONSE_TIME'   => $duration,
                    'ERROR_MESSAGE'   => $errorMsg,
                    'STATUS'          => 'failed',
                    'CREATED_AT'      => date('Y-m-d H:i:s'),
                    'UPDATED_AT'      => date('Y-m-d H:i:s'),
                ]);

                DB::table('s_email_queue')->where('ID_QUEUE', $queue->ID_QUEUE)->update([
                    'STATUS'     => $newStatus,
                    'LAST_ERROR' => substr($errorMsg, 0, 500),
                    'UPDATED_AT' => date('Y-m-d H:i:s'),
                ]);

                $failedCount++;
                $this->error("✗ Failed email #{$queue->ID_QUEUE}: {$errorMsg}");
            }
        }

        $this->info("Completed: {$sentCount} sent, {$failedCount} failed.");
        return Command::SUCCESS;
    }
}
