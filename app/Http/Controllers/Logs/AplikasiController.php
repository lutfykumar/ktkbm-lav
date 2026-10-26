<?php

namespace App\Http\Controllers\Logs;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AplikasiController extends Controller
{
    protected string $permissionRead   = 'read-advance-logs-aplikasi';
    protected string $permissionExport = 'export-advance-logs-aplikasi';
    protected string $permissionDelete = 'delete-advance-logs-aplikasi';

    protected string $logFilePath;

    public function __construct()
    {
        $this->logFilePath = storage_path('logs/laravel.log');
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

        ActivityLogService::log('Lihat System Application Logs', 'view', 'logs');

        $stats = $this->getLogFileStats();

        return view('logs.aplikasi.index', [
            'title' => 'Application Logs',
            'title_parent' => 'Logs',
            'stats' => $stats,
            'can_export' => $this->hasPerm($this->permissionExport),
            'can_delete' => $this->hasPerm($this->permissionDelete),
        ]);
    }

    public function table(Request $request): JsonResponse
    {
        if (!$this->hasPerm($this->permissionRead)) {
            return $this->jsonResponse(false, 'Anda tidak memiliki hak akses');
        }

        $levelFilter = strtolower($request->input('level', 'all'));
        $search = strtolower($request->input('search', ''));

        $entries = $this->parseLogEntries(1000);

        if ($levelFilter !== 'all') {
            $entries = array_filter($entries, function ($e) use ($levelFilter) {
                return strtolower($e['level']) === $levelFilter;
            });
        }

        if (!empty($search)) {
            $entries = array_filter($entries, function ($e) use ($search) {
                return str_contains(strtolower($e['message']), $search) ||
                       str_contains(strtolower($e['date']), $search) ||
                       str_contains(strtolower($e['env']), $search);
            });
        }

        return response()->json([
            'status' => true,
            'data' => array_values($entries),
            'total' => count($entries),
            'csrf_token_name' => '_token',
            'csrf_token_hash' => csrf_token(),
        ]);
    }

    public function download(): BinaryFileResponse|JsonResponse
    {
        if (!$this->hasPerm($this->permissionExport)) {
            abort(403, 'Akses tidak diizinkan untuk download log');
        }

        if (!File::exists($this->logFilePath)) {
            return $this->jsonResponse(false, 'File log tidak ditemukan');
        }

        ActivityLogService::log('Download File System Application Log', 'export', 'logs');

        return response()->download($this->logFilePath, 'laravel-' . date('Y-m-d_H-i-s') . '.log');
    }

    public function clear(): JsonResponse
    {
        if (!$this->hasPerm($this->permissionDelete)) {
            return $this->jsonResponse(false, 'Anda tidak memiliki hak akses untuk mengosongkan log');
        }

        try {
            if (File::exists($this->logFilePath)) {
                File::put($this->logFilePath, '');
            }

            ActivityLogService::log('Mengosongkan File System Application Log', 'delete', 'logs');

            return $this->jsonResponse(true, 'File log aplikasi berhasil dikosongkan.');
        } catch (\Throwable $e) {
            return $this->jsonResponse(false, 'Gagal mengosongkan log: ' . $e->getMessage());
        }
    }

    /**
     * Compute statistics for the log file.
     */
    protected function getLogFileStats(): array
    {
        if (!File::exists($this->logFilePath)) {
            return [
                'exists' => false,
                'size_formatted' => '0 KB',
                'total_lines' => 0,
                'total_entries' => 0,
                'errors_count' => 0,
                'warnings_count' => 0,
                'last_modified' => '-',
            ];
        }

        $bytes = File::size($this->logFilePath);
        $sizeFormatted = $bytes >= 1048576 
            ? round($bytes / 1048576, 2) . ' MB' 
            : round($bytes / 1024, 1) . ' KB';

        $lastModified = date('d M Y H:i:s', File::lastModified($this->logFilePath));

        $entries = $this->parseLogEntries(1000);
        $errorsCount = 0;
        $warningsCount = 0;

        foreach ($entries as $e) {
            $lvl = strtolower($e['level']);
            if (in_array($lvl, ['error', 'critical', 'alert', 'emergency'])) {
                $errorsCount++;
            } elseif (in_array($lvl, ['warning', 'notice'])) {
                $warningsCount++;
            }
        }

        return [
            'exists' => true,
            'size_formatted' => $sizeFormatted,
            'total_entries' => count($entries),
            'errors_count' => $errorsCount,
            'warnings_count' => $warningsCount,
            'last_modified' => $lastModified,
        ];
    }

    /**
     * Parse the latest log entries efficiently using reverse tail reading.
     */
    protected function parseLogEntries(int $maxLines = 1000): array
    {
        if (!File::exists($this->logFilePath)) {
            return [];
        }

        $content = $this->tailFile($this->logFilePath, $maxLines);
        if (empty($content)) {
            return [];
        }

        // Standard Laravel log pattern:
        // [2026-10-26 18:31:59] local.ERROR: Error message...
        $pattern = '/^\[(?<date>\d{4}-\d{2}-\d{2}[T ]\d{2}:\d{2}:\d{2}(?:\.\d+)?(?:[+-]\d{2}:?\d{2}|Z)?)\] (?<env>\w+)\.(?<level>[A-Za-z]+): (?<message>.*?)(?=\n\[\d{4}-\d{2}-\d{2}|$)/ms';

        preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);

        $parsed = [];
        $id = 1;
        foreach ($matches as $m) {
            $fullMessage = trim($m['message']);
            $lines = explode("\n", $fullMessage);
            $shortMessage = $lines[0] ?? '';
            $stackTrace = count($lines) > 1 ? implode("\n", array_slice($lines, 1)) : null;

            $parsed[] = [
                'id' => $id++,
                'date' => $m['date'],
                'env' => $m['env'],
                'level' => strtoupper($m['level']),
                'short_message' => $shortMessage,
                'full_message' => $fullMessage,
                'stack_trace' => $stackTrace,
            ];
        }

        // Order descending (most recent first)
        return array_reverse($parsed);
    }

    /**
     * Read the last N lines of a file efficiently without loading the whole file into memory.
     */
    protected function tailFile(string $filePath, int $lines = 1000): string
    {
        $handle = @fopen($filePath, 'r');
        if (!$handle) return '';

        $buffer = 4096;
        fseek($handle, 0, SEEK_END);
        $pos = ftell($handle);
        $data = '';
        $lineCount = 0;

        while ($pos > 0 && $lineCount < $lines) {
            $readSize = min($pos, $buffer);
            $pos -= $readSize;
            fseek($handle, $pos);
            $chunk = fread($handle, $readSize);
            $data = $chunk . $data;
            $lineCount = substr_count($data, "\n");
        }

        fclose($handle);
        return $data;
    }
}
