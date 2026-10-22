<?php

namespace App\Http\Controllers\Logs;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class EmailController extends Controller
{
    protected string $permissionRead   = 'read-advance-logs-email';
    protected string $permissionExport = 'export-advance-logs-email';
    protected string $permissionDelete = 'delete-advance-logs-email';
    protected string $permissionResend = 'resend-advance-logs-email';

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

        ActivityLogService::log('Lihat List Email Logs', 'view', 'logs');

        $categories = DB::table('s_log_email')
            ->whereNotNull('EMAIL_CATEGORY')
            ->distinct()
            ->pluck('EMAIL_CATEGORY')
            ->toArray();

        $totalEmails = DB::table('s_log_email')->count();
        $todayEmails = DB::table('s_log_email')->whereDate('CREATED_AT', date('Y-m-d'))->count();
        $failedEmails = DB::table('s_log_email')->where('STATUS', 'failed')->count();
        $sentEmails = DB::table('s_log_email')->where('STATUS', 'sent')->count();

        $statistics = [
            'total_emails' => $totalEmails,
            'today_emails' => $todayEmails,
            'failed_emails' => $failedEmails,
            'sent_emails' => $sentEmails,
        ];

        return view('logs.email.index', [
            'title' => 'Email Logs',
            'title_parent' => 'Logs',
            'categories' => $categories,
            'statistics' => $statistics,
            'can_export' => $this->hasPerm($this->permissionExport),
            'can_delete' => $this->hasPerm($this->permissionDelete),
            'can_resend' => $this->hasPerm($this->permissionResend),
        ]);
    }

    public function table(Request $request): JsonResponse
    {
        if (!$this->hasPerm($this->permissionRead)) {
            return $this->jsonResponse(false, 'Unauthorized');
        }

        $query = DB::table('s_log_email');

        if ($request->filled('category')) {
            $query->where('EMAIL_CATEGORY', $request->input('category'));
        }
        if ($request->filled('status')) {
            $query->where('STATUS', $request->input('status'));
        }
        if ($request->filled('date_start')) {
            $query->whereDate('CREATED_AT', '>=', $request->input('date_start'));
        }
        if ($request->filled('date_end')) {
            $query->whereDate('CREATED_AT', '<=', $request->input('date_end'));
        }

        $logs = $query->orderBy('CREATED_AT', 'desc')->limit(500)->get();

        $canResend = $this->hasPerm($this->permissionResend);

        $data = [];
        foreach ($logs as $v) {
            $statusClass = match (strtolower($v->STATUS ?? '')) {
                'sent' => 'bg-success',
                'failed' => 'bg-danger',
                'queued' => 'bg-info',
                default => 'bg-secondary',
            };
            $statusBadge = '<span class="badge ' . $statusClass . '">' . ucfirst(e($v->STATUS ?? '-')) . '</span>';

            $action = '<div class="btn-group">';
            $action .= '<button type="button" class="btn btn-sm btn-info btn-icon waves-effect btn-detail"
                            data-bs-toggle="tooltip" title="Detail" data-id="' . $v->ID_LOG_EMAIL . '">
                        <i class="fal fa-eye"></i></button>';

            if ($canResend && strtolower($v->STATUS ?? '') === 'failed') {
                $action .= '<button type="button" class="btn btn-sm btn-warning btn-icon waves-effect ms-1 btn-resend"
                                data-bs-toggle="tooltip" title="Kirim Ulang" data-id="' . $v->ID_LOG_EMAIL . '">
                            <i class="fal fa-redo"></i></button>';
            }
            $action .= '</div>';

            $data[] = [
                $v->ID_LOG_EMAIL,
                date('d/m/Y H:i', strtotime($v->CREATED_AT)),
                e($v->EMAIL_TO ?? '-'),
                e($v->SUBJECT ?? '-'),
                e($v->EMAIL_CATEGORY ?? '-'),
                $statusBadge,
                ($v->RESPONSE_TIME ?? 0) . ' ms',
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
        $log = DB::table('s_log_email')->where('ID_LOG_EMAIL', $id)->first();

        if (!$log) {
            return response('<div class="alert alert-danger">Data tidak ditemukan</div>', 404);
        }

        return view('logs.email._detail', [
            'log' => $log,
        ]);
    }

    public function resend(Request $request): JsonResponse
    {
        if (!$this->hasPerm($this->permissionResend)) {
            return $this->jsonResponse(false, 'Anda tidak memiliki hak akses');
        }

        $id = $request->input('id');
        $log = DB::table('s_log_email')->where('ID_LOG_EMAIL', $id)->first();
        if (!$log) {
            return $this->jsonResponse(false, 'Log email tidak ditemukan');
        }

        DB::table('s_log_email')->where('ID_LOG_EMAIL', $id)->update([
            'STATUS' => 'queued',
            'RETRY_COUNT' => DB::raw('RETRY_COUNT + 1'),
            'UPDATED_AT' => now(),
        ]);

        ActivityLogService::log("Kirim ulang email log ID: {$id}", 'update', 'logs');

        return $this->jsonResponse(true, 'Email berhasil dimasukkan ke antrean pengiriman ulang');
    }
}
