<?php

namespace App\Http\Controllers\Logs;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;
use Carbon\Carbon;

class DashboardController extends Controller
{
    protected string $permissionRead = 'read-advance-logs-dashboard';

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

        ActivityLogService::log('Lihat Logs Unified Analytics Dashboard', 'view', 'logs');

        $metrics = $this->getMetricsData();
        $trendData = $this->getTrendData7Days();
        $recentData = $this->getRecentEvents();

        return view('logs.dashboard.index', [
            'title' => 'Logs Dashboard',
            'title_parent' => 'Logs',
            'metrics' => $metrics,
            'trendData' => $trendData,
            'recentData' => $recentData,
        ]);
    }

    public function realtime(Request $request): JsonResponse
    {
        if (!$this->hasPerm($this->permissionRead)) {
            return $this->jsonResponse(false, 'Anda tidak memiliki hak akses');
        }

        $metrics = $this->getMetricsData();
        $recentData = $this->getRecentEvents();

        return response()->json([
            'status' => true,
            'metrics' => $metrics,
            'recentData' => $recentData,
            'timestamp' => now()->translatedFormat('H:i:s'),
            'csrf_token_name' => '_token',
            'csrf_token_hash' => csrf_token(),
        ]);
    }

    /**
     * Compute current aggregate metrics across all 5 log subsystems.
     */
    protected function getMetricsData(): array
    {
        $today = Carbon::today();

        // 1. User Activities
        $activityToday = DB::table('s_user_activity_logs')
            ->whereDate('created_at', $today)
            ->count();
        $totalActivities = DB::table('s_user_activity_logs')->count();

        // 2. API Requests
        $apiToday = DB::table('s_log_api_request')
            ->whereDate('CREATED_AT', $today)
            ->count();
        $totalApi = DB::table('s_log_api_request')->count();
        $apiSuccess = DB::table('s_log_api_request')
            ->where('HTTP_STATUS_CODE', '>=', 200)
            ->where('HTTP_STATUS_CODE', '<', 300)
            ->count();
        $apiSuccessRate = $totalApi > 0 ? round(($apiSuccess / $totalApi) * 100, 1) : 100;

        // 3. Email Logs
        $emailToday = DB::table('s_log_email')
            ->whereDate('CREATED_AT', $today)
            ->count();
        $emailSent = DB::table('s_log_email')->where('STATUS', 'sent')->count();
        $emailFailed = DB::table('s_log_email')->where('STATUS', 'failed')->count();

        // 4. Error Logs (DB)
        $unresolvedErrors = DB::table('s_log_error')->where('IS_RESOLVED', 0)->count();
        $criticalErrors = DB::table('s_log_error')->where('ERROR_LEVEL', 'critical')->count();

        // 5. System File Logs
        $logPath = storage_path('logs/laravel.log');
        $fileSize = 0;
        $sizeFormatted = '0 KB';
        if (File::exists($logPath)) {
            $fileSize = File::size($logPath);
            $sizeFormatted = $fileSize >= 1048576 
                ? round($fileSize / 1048576, 2) . ' MB' 
                : round($fileSize / 1024, 1) . ' KB';
        }

        return [
            'activity_today' => $activityToday,
            'total_activities' => $totalActivities,
            'api_today' => $apiToday,
            'total_api' => $totalApi,
            'api_success_rate' => $apiSuccessRate,
            'email_today' => $emailToday,
            'email_sent' => $emailSent,
            'email_failed' => $emailFailed,
            'unresolved_errors' => $unresolvedErrors,
            'critical_errors' => $criticalErrors,
            'log_size_formatted' => $sizeFormatted,
        ];
    }

    /**
     * Get 7-day trend series for visual charts.
     */
    protected function getTrendData7Days(): array
    {
        $dates = [];
        $activityCounts = [];
        $apiCounts = [];
        $errorCounts = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dateStr = $date->format('Y-m-d');
            $dates[] = $date->translatedFormat('d M');

            $activityCounts[] = DB::table('s_user_activity_logs')
                ->whereDate('created_at', $dateStr)
                ->count();

            $apiCounts[] = DB::table('s_log_api_request')
                ->whereDate('CREATED_AT', $dateStr)
                ->count();

            $errorCounts[] = DB::table('s_log_error')
                ->whereDate('LAST_OCCURRED_AT', $dateStr)
                ->count();
        }

        return [
            'categories' => $dates,
            'activities' => $activityCounts,
            'api' => $apiCounts,
            'errors' => $errorCounts,
        ];
    }

    /**
     * Get latest events for live stream feed.
     */
    protected function getRecentEvents(): array
    {
        $recentActivities = DB::table('s_user_activity_logs as l')
            ->leftJoin('s_users as u', 'u.id', '=', 'l.user_id')
            ->select('l.id', 'l.activity_description', 'l.activity_type', 'l.created_at', 'u.username')
            ->orderBy('l.created_at', 'desc')
            ->limit(7)
            ->get();

        $recentErrors = DB::table('s_log_error')
            ->orderBy('LAST_OCCURRED_AT', 'desc')
            ->limit(7)
            ->get();

        return [
            'activities' => $recentActivities,
            'errors' => $recentErrors,
        ];
    }
}
