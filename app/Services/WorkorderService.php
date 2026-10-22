<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\Alat;

class WorkorderService
{
    /**
     * Generate Work Order sequence number according to TKBM format
     */
    public function generateNoWorkOrder(?string $tgl = null): string
    {
        $f_tgl = !empty($tgl) ? $tgl : date('Y-m-d');
        $lastWo = DB::table('t_work_order as a')
            ->whereNull('a.DELETED_AT')
            ->whereDate('a.CREATED_AT', $f_tgl)
            ->orderByDesc('a.ID_WORK_ORDER')
            ->value('a.NO_WORK_ORDER');

        if (!empty($lastWo)) {
            $no = substr($lastWo, 14, 4);
            $no_valid = intval($no) + 1;
        } else {
            $no_valid = 1;
        }

        $prefix = 'TKBM-WO-';
        $tang = date('Y', strtotime($f_tgl));
        $y = substr($tang, 2, 2);

        $dd = date('m', strtotime($f_tgl));
        $tt = date('d', strtotime($f_tgl));

        $ttt = substr($tt, 0, 1);
        $tttt = substr($tt, 1, 1);

        $kodeMonthMap = [
            '01' => 'A', '02' => 'B', '03' => 'C', '04' => 'D',
            '05' => 'E', '06' => 'F', '07' => 'G', '08' => 'H',
            '09' => 'I', '10' => 'J', '11' => 'K', '12' => 'L'
        ];
        $kodeMonth = $kodeMonthMap[$dd] ?? 'L';

        $digitCharMap = [
            '1' => 'A', '2' => 'B', '3' => 'C', '4' => 'D',
            '5' => 'E', '6' => 'F', '7' => 'G', '8' => 'H',
            '9' => 'I'
        ];
        $firstTanggal = $digitCharMap[$ttt] ?? 'Z';
        $endTanggal = $digitCharMap[$tttt] ?? 'Z';

        return $prefix . $y . '-' . $kodeMonth . $firstTanggal . $endTanggal . sprintf('%04s', $no_valid);
    }

    /**
     * List work orders for table
     */
    public function tableList(
        ?string $tgl_start = '',
        ?string $tgl_end = '',
        $pelabuhan = '',
        $client = '',
        ?string $status = '',
        int $show_deleted = 0,
        $pengawas = ''
    ): array {
        $query = DB::table('t_work_order as a')
            ->leftJoin('s_users as b', 'a.ID_PENGAWAS', '=', 'b.id')
            ->leftJoin('m_pelabuhan as c', 'a.ID_PELABUHAN', '=', 'c.ID_PELABUHAN')
            ->leftJoin('m_client as d', 'a.ID_CLIENT', '=', 'd.ID_CLIENT')
            ->leftJoin('m_kapal as e', 'a.ID_KAPAL', '=', 'e.ID_KAPAL')
            ->leftJoin('m_dermaga as f', 'a.ID_DERMAGA', '=', 'f.ID_DERMAGA')
            ->leftJoin('m_aktifitas as g', 'a.ID_AKTIFITAS', '=', 'g.ID_AKTIFITAS')
            ->leftJoin('s_users as h', 'a.CREATED_BY', '=', 'h.id')
            ->leftJoin('m_satuan as i', 'a.ID_SATUAN', '=', 'i.ID_SATUAN');

        if (!empty($tgl_start) && !empty($tgl_end)) {
            $query->whereBetween(DB::raw('DATE(a.CREATED_AT)'), [$tgl_start, $tgl_end]);
        }
        if (!empty($pelabuhan)) {
            $query->where('a.ID_PELABUHAN', $pelabuhan);
        }
        if (!empty($client)) {
            $query->where('a.ID_CLIENT', $client);
        }
        if (!empty($status)) {
            $query->where('a.STATUS', $status);
        }
        if (!empty($pengawas)) {
            $query->where('a.ID_PENGAWAS', $pengawas);
        }

        if ($show_deleted == 1) {
            $query->whereNotNull('a.DELETED_AT');
        } else {
            $query->whereNull('a.DELETED_AT');
        }

        return $query->select([
            'a.*',
            'b.username as PENGAWAS_USER',
            DB::raw("DATE_FORMAT(a.CREATED_AT, '%Y-%m-%d %H:%i') as CREATED_AT"),
            'c.NAMA as NAMA_PELABUHAN',
            'd.NAMA as NAMA_CLIENT',
            'e.NAMA as NAMA_KAPAL',
            'f.NAMA as NAMA_DERMAGA',
            'g.NAMA as NAMA_AKTIFITAS',
            'h.username as CREATED_USER',
            'i.NAMA as NAMA_SATUAN',
            DB::raw("(SELECT COUNT(*) FROM t_work_order_detail wd WHERE wd.ID_WORK_ORDER = a.ID_WORK_ORDER AND wd.DELETED_AT IS NULL) AS total_laporan")
        ])
        ->orderByDesc('a.CREATED_AT')
        ->orderByDesc('a.ID_WORK_ORDER')
        ->get()
        ->toArray();
    }

    /**
     * Get single work order with joins
     */
    public function getWorkorder(int $id): ?object
    {
        return DB::table('t_work_order as a')
            ->leftJoin('s_users as b', 'a.ID_PENGAWAS', '=', 'b.id')
            ->leftJoin('m_pelabuhan as c', 'a.ID_PELABUHAN', '=', 'c.ID_PELABUHAN')
            ->leftJoin('m_client as d', 'a.ID_CLIENT', '=', 'd.ID_CLIENT')
            ->leftJoin('m_kapal as e', 'a.ID_KAPAL', '=', 'e.ID_KAPAL')
            ->leftJoin('m_dermaga as f', 'a.ID_DERMAGA', '=', 'f.ID_DERMAGA')
            ->leftJoin('m_aktifitas as g', 'a.ID_AKTIFITAS', '=', 'g.ID_AKTIFITAS')
            ->leftJoin('m_satuan as i', 'a.ID_SATUAN', '=', 'i.ID_SATUAN')
            ->leftJoin('s_users as j', 'a.CREATED_BY', '=', 'j.id')
            ->leftJoin('s_users as k', 'a.UPDATED_BY', '=', 'k.id')
            ->where('a.ID_WORK_ORDER', $id)
            ->select([
                'a.*',
                'b.username as PENGAWAS_USER',
                'c.NAMA as NAMA_PELABUHAN',
                'd.NAMA as NAMA_CLIENT',
                'e.NAMA as NAMA_KAPAL',
                'f.NAMA as NAMA_DERMAGA',
                'g.NAMA as NAMA_AKTIFITAS',
                'i.NAMA as NAMA_SATUAN',
                'j.username as CREATED_USER',
                'k.username as UPDATED_USER',
                DB::raw("(SELECT GROUP_CONCAT(wa.NAMA_ALAT SEPARATOR ', ')
                          FROM t_work_order_alat wa
                          WHERE wa.ID_WORK_ORDER = a.ID_WORK_ORDER
                          AND wa.DELETED_AT IS NULL) AS NAMA_ALAT")
            ])
            ->first();
    }

    /**
     * Get details for a work order
     */
    public function getWorkOrderDetails(int $wo_id): array
    {
        return DB::table('t_work_order_detail as a')
            ->leftJoin('m_krk as b', 'a.ID_KRK', '=', 'b.ID_KRK')
            ->leftJoin('s_users as c', 'a.CREATED_BY', '=', 'c.id')
            ->where('a.ID_WORK_ORDER', $wo_id)
            ->whereNull('a.DELETED_AT')
            ->select([
                'a.*',
                'b.NAMA as NAMA_KRK',
                'c.username as CREATED_USER'
            ])
            ->orderByDesc('a.CREATED_AT')
            ->get()
            ->toArray();
    }

    /**
     * Get single detail
     */
    public function getWorkOrderDetail(int $id): ?object
    {
        return DB::table('t_work_order_detail')
            ->where('ID_WORK_ORDER_DETAIL', $id)
            ->first();
    }

    /**
     * Get alat pivot for a work order
     */
    public function getWoAlat(int $wo_id): array
    {
        return DB::table('t_work_order_alat as wa')
            ->leftJoin('m_alat as a', 'wa.ID_ALAT', '=', 'a.ID_ALAT')
            ->where('wa.ID_WORK_ORDER', $wo_id)
            ->whereNull('wa.DELETED_AT')
            ->select(['wa.*', 'a.NAMA'])
            ->get()
            ->toArray();
    }

    /**
     * Sync alat for a work order
     */
    public function saveWoAlat(int $wo_id, array $alat_ids = [], ?int $user_id = null): void
    {
        $now = date('Y-m-d H:i:s');
        DB::table('t_work_order_alat')
            ->where('ID_WORK_ORDER', $wo_id)
            ->update(['DELETED_AT' => $now]);

        if (!empty($alat_ids)) {
            foreach ($alat_ids as $alat_id) {
                if (empty($alat_id) || !is_numeric($alat_id)) continue;
                $alat = DB::table('m_alat')->where('ID_ALAT', $alat_id)->first();
                DB::table('t_work_order_alat')->insert([
                    'ID_WORK_ORDER' => $wo_id,
                    'ID_ALAT' => $alat_id,
                    'NAMA_ALAT' => $alat ? $alat->NAMA : null,
                    'CREATED_BY' => $user_id,
                    'UPDATED_BY' => $user_id,
                    'CREATED_AT' => $now,
                    'UPDATED_AT' => $now,
                ]);
            }
        }
    }

    /**
     * Get report data for Rekap Kegiatan Kapal Bulanan per Pelabuhan
     */
    public function getKegiatanKapalReport($pelabuhan_id, string $bulan_tahun): array
    {
        if (empty($pelabuhan_id) || empty($bulan_tahun)) {
            return [];
        }

        $sql = "SELECT
                  a.ID_WORK_ORDER,
                  a.NO_WORK_ORDER,
                  a.ID_PELABUHAN,
                  a.STATUS,
                  a.CREATED_AT AS TGL_WO,
                  b.NAMA AS NAMA_PELABUHAN,
                  c.NAMA AS NAMA_KAPAL,
                  COALESCE(NULLIF(d.SHORT, ''), d.NAMA, '-') AS NAMA_PBM,
                  d.NAMA AS FULL_CLIENT_NAME,
                  inv.NO_INVOICE,
                  COALESCE(akt.NAMA, '-') AS NAMA_AKTIFITAS,
                  COALESCE(akt.TIPE, 'bongkar') AS TIPE_AKTIFITAS,
                  sat.NAMA AS NAMA_SATUAN,
                  COALESCE(
                    (SELECT SUM(wd.HASIL) FROM t_work_order_detail wd WHERE wd.ID_WORK_ORDER = a.ID_WORK_ORDER AND wd.DELETED_AT IS NULL),
                    0
                  ) AS PARTY,
                  (SELECT MIN(wd.CREATED_AT) FROM t_work_order_detail wd WHERE wd.ID_WORK_ORDER = a.ID_WORK_ORDER AND wd.DELETED_AT IS NULL) AS TGL_MULAI_KEGIATAN,
                  (SELECT MAX(wd.CREATED_AT) FROM t_work_order_detail wd WHERE wd.ID_WORK_ORDER = a.ID_WORK_ORDER AND wd.DELETED_AT IS NULL) AS TGL_SELESAI_KEGIATAN
                FROM t_work_order a
                LEFT JOIN m_pelabuhan b ON a.ID_PELABUHAN = b.ID_PELABUHAN
                LEFT JOIN m_kapal c ON a.ID_KAPAL = c.ID_KAPAL
                LEFT JOIN m_client d ON a.ID_CLIENT = d.ID_CLIENT
                LEFT JOIN m_aktifitas akt ON a.ID_AKTIFITAS = akt.ID_AKTIFITAS
                LEFT JOIN m_satuan sat ON a.ID_SATUAN = sat.ID_SATUAN
                LEFT JOIN t_invoice inv ON inv.ID_WORK_ORDER = a.ID_WORK_ORDER AND inv.DELETED_AT IS NULL
                WHERE a.DELETED_AT IS NULL
                  AND a.ID_PELABUHAN = ?
                  AND a.STATUS NOT IN ('hide', 'cancel')
                  AND (a.STATUS IN ('progress', 'done') OR EXISTS (SELECT 1 FROM t_work_order_detail wd3 WHERE wd3.ID_WORK_ORDER = a.ID_WORK_ORDER AND wd3.DELETED_AT IS NULL))
                  AND (
                    DATE_FORMAT(a.CREATED_AT, '%Y-%m') = ?
                    OR a.ID_WORK_ORDER IN (
                      SELECT wd2.ID_WORK_ORDER FROM t_work_order_detail wd2 
                      WHERE wd2.DELETED_AT IS NULL AND DATE_FORMAT(wd2.CREATED_AT, '%Y-%m') = ?
                    )
                  )
                ORDER BY COALESCE(TGL_MULAI_KEGIATAN, a.CREATED_AT) ASC, a.ID_WORK_ORDER ASC";

        return DB::select($sql, [$pelabuhan_id, $bulan_tahun, $bulan_tahun]);
    }

    /**
     * Get WO counts for dashboard stat cards
     */
    public function dashboardCounts(?int $pengawasId = null): object
    {
        $today = date('Y-m-d');
        $query = DB::table('t_work_order as a')
            ->whereNull('a.DELETED_AT')
            ->whereRaw("LOWER(a.STATUS) != 'hide'");

        if (!empty($pengawasId)) {
            $query->where(function ($q) use ($pengawasId) {
                $q->whereNull('a.ID_PENGAWAS')
                  ->orWhere('a.ID_PENGAWAS', $pengawasId);
            });
        }

        $result = $query->selectRaw("
            COUNT(*) AS total,
            SUM(CASE WHEN DATE(a.CREATED_AT) = ? THEN 1 ELSE 0 END) AS today,
            SUM(CASE WHEN a.STATUS = 'open' THEN 1 ELSE 0 END) AS open_count,
            SUM(CASE WHEN a.STATUS = 'progress' THEN 1 ELSE 0 END) AS progress_count,
            SUM(CASE WHEN a.STATUS = 'review' THEN 1 ELSE 0 END) AS review_count,
            SUM(CASE WHEN a.STATUS = 'done' THEN 1 ELSE 0 END) AS done_count
        ", [$today])->first();

        return (object)[
            'total' => (int)($result->total ?? 0),
            'today' => (int)($result->today ?? 0),
            'open_count' => (int)($result->open_count ?? 0),
            'progress_count' => (int)($result->progress_count ?? 0),
            'review_count' => (int)($result->review_count ?? 0),
            'done_count' => (int)($result->done_count ?? 0),
        ];
    }

    /**
     * Get recent WO for dashboard table
     */
    public function dashboardRecent(?int $pengawasId = null, int $limit = 10): array
    {
        $query = DB::table('t_work_order as a')
            ->leftJoin('m_kapal as b', 'a.ID_KAPAL', '=', 'b.ID_KAPAL')
            ->leftJoin('m_aktifitas as c', 'a.ID_AKTIFITAS', '=', 'c.ID_AKTIFITAS')
            ->leftJoin('m_satuan as d', 'a.ID_SATUAN', '=', 'd.ID_SATUAN')
            ->leftJoin('m_client as e', 'a.ID_CLIENT', '=', 'e.ID_CLIENT')
            ->whereNull('a.DELETED_AT')
            ->whereRaw("LOWER(a.STATUS) != 'hide'");

        if (!empty($pengawasId)) {
            $query->where(function ($q) use ($pengawasId) {
                $q->whereNull('a.ID_PENGAWAS')
                  ->orWhere('a.ID_PENGAWAS', $pengawasId);
            });
        }

        return $query->select([
            'a.ID_WORK_ORDER',
            'a.NO_WORK_ORDER',
            'a.STATUS',
            'a.CREATED_AT',
            'b.NAMA as NAMA_KAPAL',
            'c.NAMA as NAMA_AKTIFITAS',
            'd.NAMA as NAMA_SATUAN',
            'e.NAMA as NAMA_CLIENT',
            DB::raw("(SELECT COUNT(*) FROM t_work_order_detail wd WHERE wd.ID_WORK_ORDER = a.ID_WORK_ORDER AND wd.DELETED_AT IS NULL) AS total_laporan")
        ])
        ->orderByDesc('a.CREATED_AT')
        ->limit($limit)
        ->get()
        ->toArray();
    }

    /**
     * Get monthly WO status distribution for donut chart
     */
    public function dashboardMonthly(?int $pengawasId = null): object
    {
        $currentMonth = date('Y-m');
        $query = DB::table('t_work_order as a')
            ->whereNull('a.DELETED_AT')
            ->whereRaw("LOWER(a.STATUS) != 'hide'")
            ->whereRaw("DATE_FORMAT(a.CREATED_AT, '%Y-%m') = ?", [$currentMonth]);

        if (!empty($pengawasId)) {
            $query->where(function ($q) use ($pengawasId) {
                $q->whereNull('a.ID_PENGAWAS')
                  ->orWhere('a.ID_PENGAWAS', $pengawasId);
            });
        }

        $result = $query->selectRaw("
            SUM(CASE WHEN a.STATUS = 'open' THEN 1 ELSE 0 END) AS open_count,
            SUM(CASE WHEN a.STATUS = 'progress' THEN 1 ELSE 0 END) AS progress_count,
            SUM(CASE WHEN a.STATUS = 'review' THEN 1 ELSE 0 END) AS review_count,
            SUM(CASE WHEN a.STATUS = 'done' THEN 1 ELSE 0 END) AS done_count
        ")->first();

        return (object)[
            'open_count' => (int)($result->open_count ?? 0),
            'progress_count' => (int)($result->progress_count ?? 0),
            'review_count' => (int)($result->review_count ?? 0),
            'done_count' => (int)($result->done_count ?? 0),
        ];
    }

    /**
     * Get daily report count for last 7 days (bar chart for pengawas)
     */
    public function dashboardWeekly(?int $pengawasId = null): array
    {
        $query = DB::table('t_work_order_detail as wd')
            ->leftJoin('t_work_order as a', 'wd.ID_WORK_ORDER', '=', 'a.ID_WORK_ORDER')
            ->whereNull('wd.DELETED_AT')
            ->whereRaw("LOWER(a.STATUS) != 'hide'")
            ->where('wd.CREATED_AT', '>=', DB::raw('DATE_SUB(CURDATE(), INTERVAL 6 DAY)'));

        if (!empty($pengawasId)) {
            $query->where(function ($q) use ($pengawasId) {
                $q->whereNull('a.ID_PENGAWAS')
                  ->orWhere('a.ID_PENGAWAS', $pengawasId);
            });
        }

        return $query->selectRaw("DATE(wd.CREATED_AT) as tgl, COUNT(*) as total")
            ->groupBy(DB::raw("DATE(wd.CREATED_AT)"))
            ->orderBy('tgl', 'ASC')
            ->get()
            ->toArray();
    }

    /**
     * Get 6-month WO trend (created vs done per month)
     */
    public function dashboardTrend(): array
    {
        $sql = "SELECT
                  DATE_FORMAT(months.m, '%Y-%m') AS bulan,
                  DATE_FORMAT(months.m, '%b %Y') AS label,
                  IFNULL(created.cnt, 0) AS created_count,
                  IFNULL(done.cnt, 0) AS done_count
                FROM (
                  SELECT DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL n MONTH), '%Y-%m-01') AS m
                  FROM (SELECT 0 AS n UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5) nums
                ) months
                LEFT JOIN (
                  SELECT DATE_FORMAT(CREATED_AT, '%Y-%m') AS bulan, COUNT(*) AS cnt
                  FROM t_work_order WHERE DELETED_AT IS NULL
                  GROUP BY DATE_FORMAT(CREATED_AT, '%Y-%m')
                ) created ON DATE_FORMAT(months.m, '%Y-%m') = created.bulan
                LEFT JOIN (
                  SELECT DATE_FORMAT(UPDATED_AT, '%Y-%m') AS bulan, COUNT(*) AS cnt
                  FROM t_work_order WHERE DELETED_AT IS NULL AND STATUS = 'done'
                  GROUP BY DATE_FORMAT(UPDATED_AT, '%Y-%m')
                ) done ON DATE_FORMAT(months.m, '%Y-%m') = done.bulan
                ORDER BY months.m ASC";

        return DB::select($sql);
    }

    /**
     * Get top 5 clients by WO count
     */
    public function dashboardTopClients(int $limit = 5): array
    {
        return DB::table('t_work_order as a')
            ->leftJoin('m_client as c', 'a.ID_CLIENT', '=', 'c.ID_CLIENT')
            ->whereNull('a.DELETED_AT')
            ->whereNotNull('c.NAMA')
            ->selectRaw('c.NAMA as client_name, COUNT(a.ID_WORK_ORDER) as total')
            ->groupBy('a.ID_CLIENT', 'c.NAMA')
            ->orderByDesc('total')
            ->limit($limit)
            ->get()
            ->toArray();
    }
}
