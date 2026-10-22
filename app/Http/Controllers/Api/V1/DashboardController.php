<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends BaseApiController
{
    /**
     * GET /api/v1/dashboard
     */
    public function index(Request $request): JsonResponse
    {
        $pengawasId = $this->isAdmin($request) ? null : $this->userId($request);
        $today = date('Y-m-d');
        $month = date('Y-m');

        // 1. Stat cards counts
        $countsQuery = DB::table('t_work_order as a')
            ->whereNull('a.DELETED_AT')
            ->whereRaw("LOWER(a.STATUS) != 'hide'");

        if (!empty($pengawasId)) {
            $countsQuery->where(function ($q) use ($pengawasId) {
                $q->whereNull('a.ID_PENGAWAS')->orWhere('a.ID_PENGAWAS', $pengawasId);
            });
        }

        $counts = $countsQuery->selectRaw("
            COUNT(*) AS total,
            SUM(CASE WHEN DATE(a.CREATED_AT) = ? THEN 1 ELSE 0 END) AS today,
            SUM(CASE WHEN a.STATUS = 'open' THEN 1 ELSE 0 END) AS open_count,
            SUM(CASE WHEN a.STATUS = 'progress' THEN 1 ELSE 0 END) AS progress_count,
            SUM(CASE WHEN a.STATUS = 'review' THEN 1 ELSE 0 END) AS review_count,
            SUM(CASE WHEN a.STATUS = 'done' THEN 1 ELSE 0 END) AS done_count
        ", [$today])->first();

        // 2. Recent WO
        $recentQuery = DB::table('t_work_order as a')
            ->leftJoin('m_kapal as b', 'a.ID_KAPAL', '=', 'b.ID_KAPAL')
            ->leftJoin('m_aktifitas as c', 'a.ID_AKTIFITAS', '=', 'c.ID_AKTIFITAS')
            ->leftJoin('m_satuan as d', 'a.ID_SATUAN', '=', 'd.ID_SATUAN')
            ->leftJoin('m_client as e', 'a.ID_CLIENT', '=', 'e.ID_CLIENT')
            ->whereNull('a.DELETED_AT')
            ->whereRaw("LOWER(a.STATUS) != 'hide'");

        if (!empty($pengawasId)) {
            $recentQuery->where(function ($q) use ($pengawasId) {
                $q->whereNull('a.ID_PENGAWAS')->orWhere('a.ID_PENGAWAS', $pengawasId);
            });
        }

        $recentRaw = $recentQuery->select(
            'a.ID_WORK_ORDER',
            'a.NO_WORK_ORDER',
            'a.STATUS',
            'a.CREATED_AT',
            'b.NAMA as NAMA_KAPAL',
            'c.NAMA as NAMA_AKTIFITAS',
            'd.NAMA as NAMA_SATUAN',
            'e.NAMA as NAMA_CLIENT',
            DB::raw("(SELECT COUNT(*) FROM t_work_order_detail wd WHERE wd.ID_WORK_ORDER = a.ID_WORK_ORDER AND wd.DELETED_AT IS NULL) AS total_laporan")
        )->orderBy('a.CREATED_AT', 'DESC')->limit(10)->get();

        $recent = $recentRaw->map(function ($row) {
            return [
                'id'             => (int) $row->ID_WORK_ORDER,
                'no_work_order'  => $row->NO_WORK_ORDER,
                'status'         => $row->STATUS,
                'kapal_name'     => $row->NAMA_KAPAL,
                'aktifitas_name' => $row->NAMA_AKTIFITAS,
                'satuan_name'    => $row->NAMA_SATUAN,
                'client_name'    => $row->NAMA_CLIENT,
                'total_laporan'  => (int) $row->total_laporan,
                'created_at'     => $row->CREATED_AT,
            ];
        });

        // 3. Monthly status
        $monthlyQuery = DB::table('t_work_order as a')
            ->whereNull('a.DELETED_AT')
            ->whereRaw("LOWER(a.STATUS) != 'hide'")
            ->whereRaw("DATE_FORMAT(a.CREATED_AT, '%Y-%m') = ?", [$month]);

        if (!empty($pengawasId)) {
            $monthlyQuery->where(function ($q) use ($pengawasId) {
                $q->whereNull('a.ID_PENGAWAS')->orWhere('a.ID_PENGAWAS', $pengawasId);
            });
        }

        $monthly = $monthlyQuery->selectRaw("
            SUM(CASE WHEN a.STATUS = 'open' THEN 1 ELSE 0 END) AS open_count,
            SUM(CASE WHEN a.STATUS = 'progress' THEN 1 ELSE 0 END) AS progress_count,
            SUM(CASE WHEN a.STATUS = 'review' THEN 1 ELSE 0 END) AS review_count,
            SUM(CASE WHEN a.STATUS = 'done' THEN 1 ELSE 0 END) AS done_count
        ")->first();

        // 4. Weekly reports
        $weeklyQuery = DB::table('t_work_order_detail as wd')
            ->leftJoin('t_work_order as a', 'wd.ID_WORK_ORDER', '=', 'a.ID_WORK_ORDER')
            ->whereNull('wd.DELETED_AT')
            ->whereRaw("LOWER(a.STATUS) != 'hide'")
            ->where('wd.CREATED_AT', '>=', DB::raw('DATE_SUB(CURDATE(), INTERVAL 6 DAY)'));

        if (!empty($pengawasId)) {
            $weeklyQuery->where(function ($q) use ($pengawasId) {
                $q->whereNull('a.ID_PENGAWAS')->orWhere('a.ID_PENGAWAS', $pengawasId);
            });
        }

        $weeklyRaw = $weeklyQuery->select(
            DB::raw('DATE(wd.CREATED_AT) AS tgl'),
            DB::raw('COUNT(*) AS total')
        )->groupBy(DB::raw('DATE(wd.CREATED_AT)'))->orderBy('tgl', 'ASC')->get();

        $weekly = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-{$i} days"));
            $weekly[$d] = 0;
        }
        foreach ($weeklyRaw as $row) {
            if (isset($weekly[$row->tgl])) {
                $weekly[$row->tgl] = (int) $row->total;
            }
        }

        $weeklyLabels = array_map(function ($d) {
            return date('d M', strtotime($d));
        }, array_keys($weekly));

        return $this->jsonSuccess('Dashboard berhasil dimuat', [
            'counts' => [
                'total'          => (int) ($counts->total ?? 0),
                'today'          => (int) ($counts->today ?? 0),
                'open_count'     => (int) ($counts->open_count ?? 0),
                'progress_count' => (int) ($counts->progress_count ?? 0),
                'review_count'   => (int) ($counts->review_count ?? 0),
                'done_count'     => (int) ($counts->done_count ?? 0),
            ],
            'recent_wo' => $recent,
            'monthly' => [
                'open'     => (int) ($monthly->open_count ?? 0),
                'progress' => (int) ($monthly->progress_count ?? 0),
                'review'   => (int) ($monthly->review_count ?? 0),
                'done'     => (int) ($monthly->done_count ?? 0),
            ],
            'weekly' => [
                'labels' => $weeklyLabels,
                'data'   => array_values($weekly),
            ],
        ]);
    }
}
