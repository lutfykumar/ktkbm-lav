<?php

namespace App\Http\Controllers;

use App\Services\WorkorderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DashboardController extends Controller
{
    protected WorkorderService $woService;

    public function __construct(WorkorderService $woService)
    {
        $this->woService = $woService;
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        // Check permission if not superadmin (role 1)
        if ($user->group_id !== 1 && !Gate::allows('read-dashboard')) {
            abort(403, 'You do not have permission to access this page');
        }

        $isAdmin = in_array($user->group_id, [1, 2, 3]);

        if (!$isAdmin) {
            // Pengawas Dashboard
            $pengawasId = $user->id;
            $counts = $this->woService->dashboardCounts($pengawasId);
            $recentWo = $this->woService->dashboardRecent($pengawasId, 5);
            $monthly = $this->woService->dashboardMonthly($pengawasId);

            $weeklyRaw = $this->woService->dashboardWeekly($pengawasId);
            $weekly = [];
            for ($i = 6; $i >= 0; $i--) {
                $d = date('Y-m-d', strtotime("-{$i} days"));
                $weekly[$d] = 0;
            }
            foreach ($weeklyRaw as $row) {
                if (isset($weekly[$row->tgl])) {
                    $weekly[$row->tgl] = (int)$row->total;
                }
            }

            $weeklyLabels = json_encode(array_map(fn($d) => date('d M', strtotime($d)), array_keys($weekly)));
            $weeklyData = json_encode(array_values($weekly));

            return view('dashboard_pengawas', [
                'title' => 'Dashboard Pengawas',
                'counts' => $counts,
                'recent_wo' => $recentWo,
                'monthly' => $monthly,
                'weekly_labels' => $weeklyLabels,
                'weekly_data' => $weeklyData,
                'username' => $user->username,
            ]);
        }

        // Admin Dashboard
        $counts = $this->woService->dashboardCounts();
        $recentWo = $this->woService->dashboardRecent(null, 10);
        $monthly = $this->woService->dashboardMonthly();

        // Trend 6 bulan
        $trend = $this->woService->dashboardTrend();
        $trendLabels = [];
        $trendCreated = [];
        $trendDone = [];
        foreach ($trend as $row) {
            $trendLabels[] = $row->label;
            $trendCreated[] = (int)$row->created_count;
            $trendDone[] = (int)$row->done_count;
        }

        // Top 5 clients
        $topClients = $this->woService->dashboardTopClients(5);
        $clientNames = [];
        $clientTotals = [];
        foreach ($topClients as $row) {
            $clientNames[] = $row->client_name;
            $clientTotals[] = (int)$row->total;
        }

        return view('dashboard', [
            'title' => 'Dashboard',
            'counts' => $counts,
            'recent_wo' => $recentWo,
            'monthly' => $monthly,
            'trend_labels' => json_encode($trendLabels),
            'trend_created' => json_encode($trendCreated),
            'trend_done' => json_encode($trendDone),
            'client_names' => json_encode($clientNames),
            'client_totals' => json_encode($clientTotals),
            'username' => $user->username,
        ]);
    }
}
