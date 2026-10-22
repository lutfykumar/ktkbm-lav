<?php

namespace App\Http\Controllers\Logs;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ErrorsController extends Controller
{
    protected string $permissionRead   = 'read-advance-logs-errors';
    protected string $permissionExport = 'export-advance-logs-errors';
    protected string $permissionUpdate = 'update-advance-logs-errors';

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

        ActivityLogService::log('Lihat List Error Logs', 'view', 'logs');

        $totalErrors = DB::table('s_log_error')->count();
        $unresolvedErrors = DB::table('s_log_error')->where('IS_RESOLVED', 0)->count();
        $criticalErrors = DB::table('s_log_error')->where('ERROR_LEVEL', 'critical')->count();
        $todayErrors = DB::table('s_log_error')->whereDate('LAST_OCCURRED_AT', date('Y-m-d'))->count();

        $statistics = [
            'total_errors' => $totalErrors,
            'unresolved_errors' => $unresolvedErrors,
            'critical_errors' => $criticalErrors,
            'today_errors' => $todayErrors,
        ];

        return view('logs.errors.index', [
            'title' => 'Error Logs',
            'title_parent' => 'Logs',
            'statistics' => $statistics,
            'can_export' => $this->hasPerm($this->permissionExport),
            'can_update' => $this->hasPerm($this->permissionUpdate),
        ]);
    }

    public function getData(Request $request): JsonResponse
    {
        if (!$this->hasPerm($this->permissionRead)) {
            return $this->jsonResponse(false, 'Unauthorized');
        }

        $query = DB::table('s_log_error');

        if ($request->filled('level')) {
            $query->where('ERROR_LEVEL', $request->input('level'));
        }
        if ($request->filled('is_resolved')) {
            $query->where('IS_RESOLVED', (int)$request->input('is_resolved'));
        }
        if ($request->filled('date_start')) {
            $query->whereDate('LAST_OCCURRED_AT', '>=', $request->input('date_start'));
        }
        if ($request->filled('date_end')) {
            $query->whereDate('LAST_OCCURRED_AT', '<=', $request->input('date_end'));
        }

        $logs = $query->orderBy('LAST_OCCURRED_AT', 'desc')->limit(500)->get();

        $canUpdate = $this->hasPerm($this->permissionUpdate);

        $data = [];
        foreach ($logs as $v) {
            $levelClass = match (strtolower($v->ERROR_LEVEL ?? '')) {
                'critical', 'fatal' => 'bg-danger',
                'warning' => 'bg-warning',
                'info' => 'bg-info',
                default => 'bg-secondary',
            };
            $levelBadge = '<span class="badge ' . $levelClass . '">' . ucfirst(e($v->ERROR_LEVEL ?? 'error')) . '</span>';

            $statusBadge = $v->IS_RESOLVED
                ? '<span class="badge bg-success">Resolved</span>'
                : '<span class="badge bg-danger">Open</span>';

            $action = '<div class="btn-group">';
            $action .= '<button type="button" class="btn btn-sm btn-info btn-icon waves-effect btn-detail"
                            data-bs-toggle="tooltip" title="Detail" data-id="' . $v->ID_LOG_ERROR . '">
                        <i class="fal fa-eye"></i></button>';

            if ($canUpdate && !$v->IS_RESOLVED) {
                $action .= '<button type="button" class="btn btn-sm btn-success btn-icon waves-effect ms-1 btn-resolve"
                                data-bs-toggle="tooltip" title="Tandai Selesai" data-id="' . $v->ID_LOG_ERROR . '">
                            <i class="fal fa-check"></i></button>';
            }
            $action .= '</div>';

            $data[] = [
                $v->ID_LOG_ERROR,
                $v->LAST_OCCURRED_AT ? date('d/m/Y H:i', strtotime($v->LAST_OCCURRED_AT)) : '-',
                $levelBadge,
                e($v->ERROR_TYPE ?? '-'),
                e(mb_strimwidth($v->ERROR_MESSAGE ?? '-', 0, 80, '...')),
                e(($v->ERROR_FILE ?? '-') . ':' . ($v->ERROR_LINE ?? '-')),
                $v->OCCURRENCE_COUNT ?? 1,
                $statusBadge,
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
        $log = DB::table('s_log_error as e')
            ->leftJoin('s_users as u', 'u.id', '=', 'e.USER_ID')
            ->where('e.ID_LOG_ERROR', $id)
            ->select('e.*', 'u.username as user_name')
            ->first();

        if (!$log) {
            return response('<div class="alert alert-danger">Data tidak ditemukan</div>', 404);
        }

        return view('logs.errors._detail', [
            'log' => $log,
        ]);
    }

    public function resolve(Request $request): JsonResponse
    {
        if (!$this->hasPerm($this->permissionUpdate)) {
            return $this->jsonResponse(false, 'Anda tidak memiliki hak akses');
        }

        $id = $request->input('id');
        $notes = $request->input('notes', 'Resolved via web dashboard');

        DB::table('s_log_error')->where('ID_LOG_ERROR', $id)->update([
            'IS_RESOLVED' => 1,
            'RESOLVED_AT' => now(),
            'RESOLVED_BY' => auth()->id(),
            'RESOLUTION_NOTES' => $notes,
        ]);

        ActivityLogService::log("Resolve error log ID: {$id}", 'update', 'logs');

        return $this->jsonResponse(true, 'Error berhasil ditandai telah diselesaikan');
    }
}
