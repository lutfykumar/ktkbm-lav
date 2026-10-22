<?php

namespace App\Http\Controllers\Logs;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ApiController extends Controller
{
    protected string $permissionRead   = 'read-advance-logs-api';
    protected string $permissionExport = 'export-advance-logs-api';
    protected string $permissionDelete = 'delete-advance-logs-api';

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

        ActivityLogService::log('Lihat List API Request Logs', 'view', 'logs');

        $totalRequests = DB::table('s_log_api_request')->count();
        $todayRequests = DB::table('s_log_api_request')->whereDate('CREATED_AT', date('Y-m-d'))->count();
        $avgResponseTime = DB::table('s_log_api_request')->avg('RESPONSE_TIME_MS') ?? 0;
        $errorRequests = DB::table('s_log_api_request')->where('HTTP_STATUS_CODE', '>=', 400)->count();

        $statistics = [
            'total_requests' => $totalRequests,
            'today_requests' => $todayRequests,
            'avg_response_time' => round($avgResponseTime, 2),
            'error_requests' => $errorRequests,
        ];

        return view('logs.api.index', [
            'title' => 'API Request',
            'title_parent' => 'Logs',
            'statistics' => $statistics,
            'can_export' => $this->hasPerm($this->permissionExport),
            'can_delete' => $this->hasPerm($this->permissionDelete),
        ]);
    }

    public function getData(Request $request): JsonResponse
    {
        if (!$this->hasPerm($this->permissionRead)) {
            return $this->jsonResponse(false, 'Unauthorized');
        }

        $query = DB::table('s_log_api_request as l')
            ->leftJoin('s_users as u', 'u.id', '=', 'l.USER_ID')
            ->select('l.*', 'u.username as user_name');

        if ($request->filled('method')) {
            $query->where('l.HTTP_METHOD', $request->input('method'));
        }
        if ($request->filled('status_code')) {
            $query->where('l.HTTP_STATUS_CODE', $request->input('status_code'));
        }
        if ($request->filled('date_start')) {
            $query->whereDate('l.CREATED_AT', '>=', $request->input('date_start'));
        }
        if ($request->filled('date_end')) {
            $query->whereDate('l.CREATED_AT', '<=', $request->input('date_end'));
        }

        $logs = $query->orderBy('l.CREATED_AT', 'desc')->limit(500)->get();

        $data = [];
        foreach ($logs as $v) {
            $methodClass = match (strtoupper($v->HTTP_METHOD)) {
                'GET' => 'bg-info',
                'POST' => 'bg-success',
                'PUT', 'PATCH' => 'bg-warning',
                'DELETE' => 'bg-danger',
                default => 'bg-secondary',
            };
            $methodBadge = '<span class="badge ' . $methodClass . '">' . e($v->HTTP_METHOD) . '</span>';

            $codeClass = ($v->HTTP_STATUS_CODE >= 400) ? 'bg-danger' : (($v->HTTP_STATUS_CODE >= 300) ? 'bg-warning' : 'bg-success');
            $statusBadge = '<span class="badge ' . $codeClass . '">' . e($v->HTTP_STATUS_CODE) . '</span>';

            $action = '<button type="button" class="btn btn-sm btn-info btn-icon waves-effect btn-detail"
                            data-bs-toggle="tooltip" title="Detail" data-id="' . $v->ID_LOG_API . '">
                        <i class="fal fa-eye"></i></button>';

            $data[] = [
                $v->ID_LOG_API,
                date('d/m/Y H:i:s', strtotime($v->CREATED_AT)),
                $methodBadge,
                e($v->ENDPOINT ?? $v->FULL_URL ?? '-'),
                $statusBadge,
                ($v->RESPONSE_TIME_MS ?? 0) . ' ms',
                e($v->IP_ADDRESS ?? '-'),
                e($v->user_name ?: '-'),
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
        $log = DB::table('s_log_api_request as l')
            ->leftJoin('s_users as u', 'u.id', '=', 'l.USER_ID')
            ->where('l.ID_LOG_API', $id)
            ->select('l.*', 'u.username as user_name')
            ->first();

        if (!$log) {
            return response('<div class="alert alert-danger">Data tidak ditemukan</div>', 404);
        }

        return view('logs.api._detail', [
            'log' => $log,
        ]);
    }
}
