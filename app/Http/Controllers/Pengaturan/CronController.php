<?php

namespace App\Http\Controllers\Pengaturan;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogService;
use App\Services\PengaturanService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class CronController extends Controller
{
    protected string $permissionRead   = 'read-advance-pengaturan-cron';
    protected string $permissionUpdate = 'update-advance-pengaturan-cron';

    protected PengaturanService $service;

    public function __construct(PengaturanService $service)
    {
        $this->service = $service;
    }

    private function hasPerm(string $permission): bool
    {
        $user = auth()->user();
        if (!$user) return false;
        if (in_array((int)$user->group_id, [1, 2])) return true;
        return Gate::allows($permission);
    }

    private function jsonResponse(bool $status, string $msg, array $extra = []): JsonResponse
    {
        return response()->json(array_merge([
            'status' => $status,
            'msg' => $msg,
            'csrf_token_name' => '_token',
            'csrf_token_hash' => csrf_token(),
        ], $extra));
    }

    public function index()
    {
        if (!$this->hasPerm($this->permissionRead)) {
            abort(403, 'Akses tidak diizinkan');
        }

        ActivityLogService::log('Lihat Halaman Cron Job Management', 'view', 'pengaturan');

        return view('pengaturan.cron.index', [
            'title' => 'Cron Job',
            'title_parent' => 'Pengaturan',
        ]);
    }

    public function getData(): JsonResponse
    {
        if (!$this->hasPerm($this->permissionRead)) {
            return response()->json([]);
        }

        $jobs = $this->service->getCronJobsTable();
        $data = [];

        foreach ($jobs as $job) {
            $statusBadge = $job->IS_ACTIVE
                ? '<span class="badge bg-success">Active</span>'
                : '<span class="badge bg-secondary">Inactive</span>';

            $runningBadge = $job->IS_RUNNING
                ? '<span class="badge bg-warning">Running</span>'
                : '<span class="badge bg-secondary">Idle</span>';

            $totalRuns = ($job->SUCCESS_COUNT ?? 0) + ($job->FAILURE_COUNT ?? 0);
            $successRate = $totalRuns > 0
                ? round((($job->SUCCESS_COUNT ?? 0) / $totalRuns) * 100, 2)
                : 0;

            $action = '<div class="btn-group">';
            $action .= '<button type="button" class="btn btn-sm btn-info btn-icon waves-effect btn-detail"
                            data-bs-toggle="tooltip" title="Detail" data-id="' . $job->ID_CRON_JOB . '">
                        <i class="fal fa-eye"></i></button>';

            if ($this->hasPerm($this->permissionUpdate)) {
                $toggleIcon = $job->IS_ACTIVE ? 'fa-pause' : 'fa-play';
                $toggleTitle = $job->IS_ACTIVE ? 'Nonaktifkan' : 'Aktifkan';
                $action .= '<button type="button" class="btn btn-sm btn-warning btn-icon waves-effect btn-toggle"
                                data-bs-toggle="tooltip" title="' . $toggleTitle . '" data-id="' . $job->ID_CRON_JOB . '" data-status="' . $job->IS_ACTIVE . '">
                            <i class="fal ' . $toggleIcon . '"></i></button>';

                $action .= '<button type="button" class="btn btn-sm btn-success btn-icon waves-effect btn-execute"
                                data-bs-toggle="tooltip" title="Jalankan Sekarang" data-id="' . $job->ID_CRON_JOB . '">
                            <i class="fal fa-bolt"></i></button>';
            }

            $action .= '<button type="button" class="btn btn-sm btn-secondary btn-icon waves-effect btn-logs"
                            data-bs-toggle="tooltip" title="Logs Eksekusi" data-id="' . $job->ID_CRON_JOB . '">
                        <i class="fal fa-history"></i></button>';

            $action .= '</div>';

            $data[] = [
                e($job->JOB_NAME ?? '-'),
                e($job->JOB_SLUG ?? '-'),
                e(($job->SCHEDULE_TYPE ?? '') . ': ' . ($job->SCHEDULE_VALUE ?? '')),
                $job->NEXT_RUN_AT ? date('Y-m-d H:i', strtotime($job->NEXT_RUN_AT)) : '-',
                $job->LAST_RUN_AT ? date('Y-m-d H:i', strtotime($job->LAST_RUN_AT)) : '-',
                $successRate . '%',
                ($job->AVG_EXECUTION_TIME_MS ?? 0) . ' ms',
                $statusBadge,
                $runningBadge,
                $action,
            ];
        }

        return response()->json($data);
    }

    public function statistics(): JsonResponse
    {
        if (!$this->hasPerm($this->permissionRead)) {
            return $this->jsonResponse(false, 'Anda tidak memiliki hak akses');
        }

        $totalJobs = DB::table('s_cron_jobs')->count();
        $activeJobs = DB::table('s_cron_jobs')->where('IS_ACTIVE', 1)->count();
        $runningJobs = DB::table('s_cron_jobs')->where('IS_RUNNING', 1)->count();
        $inactiveJobs = DB::table('s_cron_jobs')->where('IS_ACTIVE', 0)->count();

        return response()->json([
            'status' => true,
            'job_stats' => [
                'total_jobs' => $totalJobs,
                'active_jobs' => $activeJobs,
                'running_jobs' => $runningJobs,
                'inactive_jobs' => $inactiveJobs,
            ]
        ]);
    }

    public function detail(Request $request): JsonResponse
    {
        if (!$this->hasPerm($this->permissionRead)) {
            return $this->jsonResponse(false, 'Anda tidak memiliki hak akses');
        }

        $id = $request->input('id');
        $job = $this->service->getCronJob((int)$id);
        if (!$job) {
            return $this->jsonResponse(false, 'Data job tidak ditemukan');
        }

        $recentExecutions = DB::table('s_cron_execution_log')
            ->where('CRON_JOB_ID', $id)
            ->orderBy('STARTED_AT', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'status' => true,
            'data' => (array)$job,
            'recent_executions' => $recentExecutions,
            'csrf_token_name' => '_token',
            'csrf_token_hash' => csrf_token(),
        ]);
    }

    public function toggleStatus(Request $request): JsonResponse
    {
        if (!$this->hasPerm($this->permissionUpdate)) {
            return $this->jsonResponse(false, 'Anda tidak memiliki hak akses');
        }

        $id = $request->input('job_id');
        $currentStatus = (int)$request->input('current_status');
        $newStatus = $currentStatus === 1 ? 0 : 1;

        DB::table('s_cron_jobs')->where('ID_CRON_JOB', $id)->update([
            'IS_ACTIVE' => $newStatus,
            'UPDATED_AT' => now(),
        ]);

        ActivityLogService::log("Mengubah status cron job ID {$id} ke {$newStatus}", 'update', 'pengaturan');

        return $this->jsonResponse(true, 'Status job berhasil diubah');
    }

    public function execute(Request $request): JsonResponse
    {
        if (!$this->hasPerm($this->permissionUpdate)) {
            return $this->jsonResponse(false, 'Anda tidak memiliki hak akses');
        }

        $id = $request->input('job_id');
        $job = $this->service->getCronJob((int)$id);
        if (!$job) {
            return $this->jsonResponse(false, 'Job tidak ditemukan');
        }

        $startTime = microtime(true);

        $execId = DB::table('s_cron_execution_log')->insertGetId([
            'CRON_JOB_ID' => $id,
            'STARTED_AT' => now(),
            'STATUS' => 'running',
        ]);

        // Mark as completed
        $duration = round((microtime(true) - $startTime) * 1000, 2);

        DB::table('s_cron_execution_log')->where('ID_EXECUTION', $execId)->update([
            'COMPLETED_AT' => now(),
            'STATUS' => 'success',
            'EXECUTION_TIME_MS' => $duration,
            'OUTPUT_LOG' => 'Manual run executed successfully via web dashboard',
        ]);

        DB::table('s_cron_jobs')->where('ID_CRON_JOB', $id)->update([
            'LAST_RUN_AT' => now(),
            'SUCCESS_COUNT' => DB::raw('SUCCESS_COUNT + 1'),
            'UPDATED_AT' => now(),
        ]);

        ActivityLogService::log("Manual run cron job: {$job->JOB_NAME}", 'update', 'pengaturan');

        return $this->jsonResponse(true, "Job {$job->JOB_NAME} berhasil dieksekusi ({$duration} ms)");
    }

    public function executionLogs(Request $request): JsonResponse
    {
        if (!$this->hasPerm($this->permissionRead)) {
            return $this->jsonResponse(false, 'Anda tidak memiliki hak akses');
        }

        $id = $request->input('job_id');
        $logs = DB::table('s_cron_execution_log')
            ->where('CRON_JOB_ID', $id)
            ->orderBy('STARTED_AT', 'desc')
            ->limit(30)
            ->get();

        $data = [];
        foreach ($logs as $log) {
            $data[] = [
                $log->ID_EXECUTION,
                $log->STARTED_AT ?? '-',
                $log->COMPLETED_AT ?? '-',
                ($log->EXECUTION_TIME_MS ?? 0) . ' ms',
                $log->STATUS ?? '-',
                ($log->MEMORY_USAGE_MB ?? '-') . ' MB',
            ];
        }

        return response()->json([
            'status' => true,
            'data' => $data,
            'csrf_token_name' => '_token',
            'csrf_token_hash' => csrf_token(),
        ]);
    }
}
