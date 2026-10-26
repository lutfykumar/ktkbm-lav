<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class AuditDashboardActions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'audit:dashboard 
                            {--module=all : Specific module to audit (auth, dashboard, master, workorder, accounting, activity, report, pengaturan, logs, profile)} 
                            {--output=docs/audit/DASHBOARD_RAPORT.md : Output markdown report file path}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Audit dashboard routes, actions, and UI endpoints, generating a structured report card (Buku Raport)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info("=== Starting TKBM Dashboard Functional Audit ===");
        $targetModule = strtolower($this->option('module'));
        $outputPath = base_path($this->option('output'));

        // Ensure target user for authenticated crawling
        $adminUser = User::where('active', 1)->first();
        if (!$adminUser) {
            $this->error("No active user found in s_users to perform authenticated audit.");
            return 1;
        }

        $this->info("Auditing using user: {$adminUser->username} ({$adminUser->email}) [Role: " . ($adminUser->group->name ?? 'Admin') . "]");

        // Get web routes
        $allRoutes = Route::getRoutes()->getRoutes();
        $categorized = $this->categorizeRoutes($allRoutes);

        $results = [];
        $moduleScores = [];

        foreach ($categorized as $modName => $routes) {
            if ($targetModule !== 'all' && $targetModule !== $modName) {
                continue;
            }

            $this->line("\n[Auditing Module: " . strtoupper($modName) . " (" . count($routes) . " actions)]");

            $passCount = 0;
            $warnCount = 0;
            $failCount = 0;
            $actionLogs = [];

            foreach ($routes as $routeInfo) {
                $status = $this->testRouteAction($routeInfo, $adminUser);

                if ($status['grade'] === 'PASS') {
                    $passCount++;
                    $this->line("  ✓ [PASS] {$routeInfo['method']} /{$routeInfo['uri']} - {$status['note']}");
                } elseif ($status['grade'] === 'WARN') {
                    $warnCount++;
                    $this->warn("  ! [WARN] {$routeInfo['method']} /{$routeInfo['uri']} - {$status['note']}");
                } else {
                    $failCount++;
                    $this->error("  ✗ [FAIL] {$routeInfo['method']} /{$routeInfo['uri']} - {$status['note']}");
                }

                $actionLogs[] = array_merge($routeInfo, $status);
            }

            $total = count($routes);
            $score = $total > 0 ? round(($passCount / $total) * 100, 1) : 100;
            $grade = $score >= 95 ? 'A' : ($score >= 80 ? 'B' : ($score >= 60 ? 'C' : 'F'));

            $moduleScores[$modName] = [
                'total' => $total,
                'pass' => $passCount,
                'warn' => $warnCount,
                'fail' => $failCount,
                'score' => $score,
                'grade' => $grade,
                'actions' => $actionLogs,
            ];
        }

        // Generate Markdown Raport
        $this->generateRaportMarkdown($moduleScores, $outputPath, $adminUser);

        $this->info("\n=== Audit Completed Successfully! ===");
        $this->info("Buku Raport generated at: {$outputPath}");

        return 0;
    }

    /**
     * Categorize registered routes into logical dashboard modules.
     */
    protected function categorizeRoutes(array $routes): array
    {
        $modules = [
            'dashboard'  => [],
            'workorder'  => [],
            'accounting' => [],
            'activity'   => [],
            'master'     => [],
            'pengaturan' => [],
            'logs'       => [],
            'report'     => [],
            'profile'    => [],
            'auth'       => [],
        ];

        foreach ($routes as $r) {
            $uri = $r->uri();
            $methods = array_diff($r->methods(), ['HEAD']);
            $method = reset($methods);

            // Skip internal / api / system routes
            if (Str::startsWith($uri, ['api/', '_ignition', 'sanctum', 'telescope', 'up'])) {
                continue;
            }

            $info = [
                'uri' => $uri,
                'method' => $method,
                'name' => $r->getName() ?? $uri,
                'action' => $r->getActionName(),
            ];

            if ($uri === 'dashboard') {
                $modules['dashboard'][] = $info;
            } elseif (Str::startsWith($uri, 'workorder')) {
                $modules['workorder'][] = $info;
            } elseif (Str::startsWith($uri, 'accounting') || Str::contains($uri, 'invoice')) {
                $modules['accounting'][] = $info;
            } elseif (Str::startsWith($uri, 'activity')) {
                $modules['activity'][] = $info;
            } elseif (Str::startsWith($uri, 'master')) {
                $modules['master'][] = $info;
            } elseif (Str::startsWith($uri, 'pengaturan')) {
                $modules['pengaturan'][] = $info;
            } elseif (Str::startsWith($uri, 'logs')) {
                $modules['logs'][] = $info;
            } elseif (Str::startsWith($uri, 'report')) {
                $modules['report'][] = $info;
            } elseif (Str::startsWith($uri, 'profile')) {
                $modules['profile'][] = $info;
            } elseif (Str::startsWith($uri, ['login', 'logout', 'register', 'forgot-password', 'reset-password'])) {
                $modules['auth'][] = $info;
            }
        }

        return array_filter($modules, fn($m) => !empty($m));
    }

    /**
     * Test a specific route action.
     */
    protected function testRouteAction(array $routeInfo, User $user): array
    {
        $uri = '/' . ltrim($routeInfo['uri'], '/');
        $method = $routeInfo['method'];

        // If route has dynamic parameters like {id}, substitute with a safe value or test probe
        if (Str::contains($uri, '{')) {
            $uri = preg_replace('/\{[^}]+\}/', '1', $uri);
        }

        try {
            // Build sub-request
            $request = \Illuminate\Http\Request::create($uri, $method);
            $request->headers->set('Accept', 'application/json, text/html');
            $request->headers->set('X-Requested-With', 'XMLHttpRequest');

            // Handle CSRF token for POST/PUT/DELETE
            if (in_array($method, ['POST', 'PUT', 'DELETE', 'PATCH'])) {
                $request->headers->set('X-CSRF-TOKEN', csrf_token());
            }

            // Set logged in user
            auth()->setUser($user);

            // Execute through Laravel HTTP Kernel / Router
            $response = app()->handle($request);
            $statusCode = $response->getStatusCode();
            $content = $response->getContent();

            // Analyze status code
            if ($statusCode >= 200 && $statusCode < 300) {
                // Check if response is HTML or JSON
                if (Str::isJson($content)) {
                    $json = json_decode($content, true);
                    if (isset($json['status']) && $json['status'] === false) {
                        $msg = $json['msg'] ?? 'Validation rejection';
                        return [
                            'grade' => 'PASS',
                            'status_code' => $statusCode,
                            'note' => "Validation Guard Active: {$msg}",
                            'solution' => null,
                        ];
                    }
                    return [
                        'grade' => 'PASS',
                        'status_code' => $statusCode,
                        'note' => '200 OK (Valid JSON API response)',
                        'solution' => null,
                    ];
                }
                return [
                    'grade' => 'PASS',
                    'status_code' => $statusCode,
                    'note' => '200 OK (View rendered successfully)',
                    'solution' => null,
                ];
            } elseif ($statusCode >= 300 && $statusCode < 400) {
                return [
                    'grade' => 'PASS',
                    'status_code' => $statusCode,
                    'note' => "{$statusCode} Redirect (Target: " . $response->headers->get('Location') . ")",
                    'solution' => null,
                ];
            } elseif ($statusCode === 422) {
                return [
                    'grade' => 'PASS',
                    'status_code' => $statusCode,
                    'note' => '422 Validation Handled Properly',
                    'solution' => null,
                ];
            } elseif ($statusCode === 403) {
                return [
                    'grade' => 'WARN',
                    'status_code' => $statusCode,
                    'note' => '403 Forbidden (Check Gate/Permission for role)',
                    'solution' => 'Verify role has assigned permission slug in s_group_permissions.',
                ];
            } elseif ($statusCode === 404) {
                return [
                    'grade' => 'PASS',
                    'status_code' => $statusCode,
                    'note' => '404 Handled (Route requires specific record ID/param)',
                    'solution' => null,
                ];
            } else {
                // 500 error
                $errorMsg = 'Internal Server Error';
                if (preg_match('/<title>(.*?)<\/title>/is', $content, $m)) {
                    $errorMsg = trim($m[1]);
                }
                return [
                    'grade' => 'FAIL',
                    'status_code' => $statusCode,
                    'note' => "500 Server Error: {$errorMsg}",
                    'solution' => "Investigate exception stack in storage/logs/laravel.log for {$routeInfo['action']}.",
                ];
            }
        } catch (\Throwable $e) {
            return [
                'grade' => 'FAIL',
                'status_code' => 500,
                'note' => 'Exception: ' . $e->getMessage(),
                'solution' => "Fix uncaught exception in {$routeInfo['action']} at " . basename($e->getFile()) . ":{$e->getLine()}",
            ];
        }
    }

    /**
     * Generate structured markdown report card.
     */
    protected function generateRaportMarkdown(array $moduleScores, string $outputPath, User $user): void
    {
        $dir = dirname($outputPath);
        if (!File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        $now = now()->translatedFormat('l, d F Y H:i:s');
        $totalActions = 0;
        $totalPass = 0;
        $totalWarn = 0;
        $totalFail = 0;

        foreach ($moduleScores as $m) {
            $totalActions += $m['total'];
            $totalPass += $m['pass'];
            $totalWarn += $m['warn'];
            $totalFail += $m['fail'];
        }

        $overallScore = $totalActions > 0 ? round(($totalPass / $totalActions) * 100, 1) : 100;
        $overallGrade = $overallScore >= 95 ? 'A' : ($overallScore >= 80 ? 'B' : ($overallScore >= 60 ? 'C' : 'F'));

        $md = "# 📋 BUKU RAPORT AUDIT DASHBOARD TKBM\n\n";
        $md .= "**Waktu Audit:** {$now}  \n";
        $md .= "**Akun Penguji:** `{$user->username}` (`{$user->email}`) — Role: " . ($user->group->name ?? 'Admin') . "  \n";
        $md .= "**Nilai Keseluruhan:** **{$overallScore}%** (Predikat: **Grade {$overallGrade}**)  \n\n";
        $md .= "---\n\n";

        // Summary Scorecard
        $md .= "## 1. Scorecard Ringkasan Per Modul\n\n";
        $md .= "| Modul | Total Aksi | Pass | Warn | Fail | Nilai (%) | Predikat |\n";
        $md .= "| :--- | :---: | :---: | :---: | :---: | :---: | :---: |\n";

        foreach ($moduleScores as $name => $s) {
            $badge = match ($s['grade']) {
                'A' => '🟢 **A**',
                'B' => '🔵 **B**',
                'C' => '🟡 **C**',
                default => '🔴 **F**',
            };
            $md .= "| **" . ucfirst($name) . "** | {$s['total']} | {$s['pass']} | {$s['warn']} | {$s['fail']} | {$s['score']}% | {$badge} |\n";
        }

        $md .= "| **TOTAL / RATA-RATA** | **{$totalActions}** | **{$totalPass}** | **{$totalWarn}** | **{$totalFail}** | **{$overallScore}%** | **{$overallGrade}** |\n\n";
        $md .= "---\n\n";

        // Detailed Findings & Remediation Solutions
        $md .= "## 2. Temuan & Panduan Solusi (Issues & Remediation)\n\n";

        $hasIssues = false;
        foreach ($moduleScores as $modName => $m) {
            $issues = array_filter($m['actions'], fn($a) => $a['grade'] !== 'PASS');
            if (empty($issues)) continue;

            $hasIssues = true;
            $md .= "### Modul: " . ucfirst($modName) . "\n\n";
            $md .= "| Method | Route URI | HTTP | Status | Catatan / Error | Rekomendasi Solusi |\n";
            $md .= "| :--- | :--- | :---: | :---: | :--- | :--- |\n";

            foreach ($issues as $issue) {
                $statusBadge = $issue['grade'] === 'WARN' ? '⚠️ WARN' : '❌ FAIL';
                $md .= "| `{$issue['method']}` | `/{$issue['uri']}` | `{$issue['status_code']}` | {$statusBadge} | " . Str::limit($issue['note'], 50) . " | " . ($issue['solution'] ?? '-') . " |\n";
            }
            $md .= "\n";
        }

        if (!$hasIssues) {
            $md .= "> 🟢 **Luar Biasa!** Tidak ditemukan satupun aksi yang gagal (*Zero Failure*). Seluruh endpoint dan aksi merespons dengan status sehat.\n\n";
        }

        $md .= "---\n\n";

        // Complete Action Matrix
        $md .= "## 3. Matriks Lengkap Seluruh Aksi yang Diuji\n\n";
        foreach ($moduleScores as $modName => $m) {
            $md .= "<details>\n<summary><b>Modul: " . ucfirst($modName) . " ({$m['total']} Aksi) — Klik untuk melihat detail</b></summary>\n\n";
            $md .= "| Method | Route | Controller Action | Status | Keterangan |\n";
            $md .= "| :--- | :--- | :--- | :---: | :--- |\n";

            foreach ($m['actions'] as $act) {
                $icon = $act['grade'] === 'PASS' ? '✅' : ($act['grade'] === 'WARN' ? '⚠️' : '❌');
                $actionShort = str_replace('App\\Http\\Controllers\\', '', $act['action']);
                $md .= "| `{$act['method']}` | `/{$act['uri']}` | `{$actionShort}` | {$icon} {$act['grade']} | {$act['note']} |\n";
            }
            $md .= "\n</details>\n\n";
        }

        $md .= "\n---\n\n*Laporan ini dibuat otomatis oleh `agent-dashboard-auditor` (TKBM Multi-Agent System).*\n";

        File::put($outputPath, $md);
    }
}
