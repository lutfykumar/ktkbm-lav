<?php

namespace App\Http\Controllers\Logs;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class AktifitasController extends Controller
{
    protected string $permissionRead   = 'read-advance-logs-aktifitas';
    protected string $permissionExport = 'export-advance-logs-aktifitas';
    protected string $permissionDelete = 'delete-advance-logs-aktifitas';

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

        ActivityLogService::log('Lihat List User Activity Logs', 'view', 'logs');

        $users = DB::table('s_users')->select('id', 'username')->orderBy('username')->get();
        $activityTypes = DB::table('s_user_activity_logs')
            ->whereNotNull('activity_type')
            ->distinct()
            ->pluck('activity_type')
            ->toArray();
        $activityCategories = DB::table('s_user_activity_logs')
            ->whereNotNull('activity_category')
            ->distinct()
            ->pluck('activity_category')
            ->toArray();

        $today = date('Y-m-d');
        $totalActivities = DB::table('s_user_activity_logs')->count();
        $todayActivities = DB::table('s_user_activity_logs')->whereDate('created_at', $today)->count();
        $failedLogins = DB::table('s_user_activity_logs')
            ->where('activity_type', 'login')
            ->where('activity_status', 'failed')
            ->count();
        $uniqueUsers = DB::table('s_user_activity_logs')->distinct('user_id')->count('user_id');

        $statistics = [
            'total_activities' => $totalActivities,
            'today_activities' => $todayActivities,
            'failed_logins' => $failedLogins,
            'unique_users' => $uniqueUsers,
        ];

        return view('logs.user_activity.index', [
            'title' => 'User Activity',
            'title_parent' => 'Logs',
            'users' => $users,
            'activity_types' => $activityTypes,
            'activity_categories' => $activityCategories,
            'statistics' => $statistics,
            'can_export' => $this->hasPerm($this->permissionExport),
            'can_delete' => $this->hasPerm($this->permissionDelete),
        ]);
    }

    public function table(Request $request): JsonResponse
    {
        if (!$this->hasPerm($this->permissionRead)) {
            return $this->jsonResponse(false, 'Unauthorized');
        }

        $query = DB::table('s_user_activity_logs as l')
            ->leftJoin('s_users as u', 'u.id', '=', 'l.user_id')
            ->select('l.*', 'u.username as user_name');

        if ($request->filled('date_start')) {
            $query->whereDate('l.created_at', '>=', $request->input('date_start'));
        }
        if ($request->filled('date_end')) {
            $query->whereDate('l.created_at', '<=', $request->input('date_end'));
        }
        if ($request->filled('user_id')) {
            $query->where('l.user_id', $request->input('user_id'));
        }
        if ($request->filled('activity_type')) {
            $query->where('l.activity_type', $request->input('activity_type'));
        }
        if ($request->filled('activity_category')) {
            $query->where('l.activity_category', $request->input('activity_category'));
        }
        if ($request->filled('activity_status')) {
            $query->where('l.activity_status', $request->input('activity_status'));
        }
        if ($request->filled('ip_address')) {
            $query->where('l.ip_address', 'like', '%' . $request->input('ip_address') . '%');
        }

        $logs = $query->orderBy('l.created_at', 'desc')->limit(500)->get();

        $data = [];
        foreach ($logs as $v) {
            $typeClass = match ($v->activity_type) {
                'login' => 'bg-info',
                'logout' => 'bg-secondary',
                'create' => 'bg-success',
                'update' => 'bg-warning',
                'delete' => 'bg-danger',
                default => 'bg-primary',
            };
            $typeBadge = '<span class="badge ' . $typeClass . '">' . ucfirst(e($v->activity_type ?? '-')) . '</span>';

            $catBadge = '<span class="badge bg-soft-secondary text-secondary">' . ucfirst(e($v->activity_category ?? '-')) . '</span>';

            $statusClass = ($v->activity_status === 'failed') ? 'bg-danger' : 'bg-success';
            $statusBadge = '<span class="badge ' . $statusClass . '">' . ucfirst(e($v->activity_status ?? 'success')) . '</span>';

            $action = '<button type="button" class="btn btn-sm btn-info btn-icon waves-effect btn-detail"
                            data-bs-toggle="tooltip" title="Detail" data-id="' . $v->id . '">
                        <i class="fal fa-eye"></i></button>';

            $data[] = [
                $v->id,
                date('d/m/Y H:i', strtotime($v->created_at)),
                e($v->user_name ?: ($v->username ?: '-')),
                e($v->activity_description ?: '-'),
                $typeBadge,
                $catBadge,
                $statusBadge,
                e($v->ip_address ?: '-'),
                $action,
            ];
        }

        return response()->json([
            'status' => true,
            'data' => $data,
            'csrf_token_name' => '_token',
            'csrf_token_hash' => csrf_token(),
        ]);
    }

    public function detail(Request $request)
    {
        if (!$this->hasPerm($this->permissionRead)) {
            return response('<div class="alert alert-danger">Tidak memiliki akses</div>', 403);
        }

        $id = $request->input('id');
        $log = DB::table('s_user_activity_logs as l')
            ->leftJoin('s_users as u', 'u.id', '=', 'l.user_id')
            ->where('l.id', $id)
            ->select('l.*', 'u.username as user_name')
            ->first();

        if (!$log) {
            return response('<div class="alert alert-danger">Data tidak ditemukan</div>', 404);
        }

        return view('logs.user_activity._detail', [
            'log' => $log,
        ]);
    }
}
