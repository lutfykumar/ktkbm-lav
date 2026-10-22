<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Services\PengaturanService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SyncController extends BaseApiController
{
    public function __construct(
        protected PengaturanService $pengaturanService
    ) {}

    /**
     * GET /api/v1/sync/pull?since=2026-02-25T10:00:00
     */
    public function pull(Request $request): JsonResponse
    {
        $since = $request->input('since');
        $pengawasId = $this->isAdmin($request) ? null : $this->userId($request);

        if (empty($since)) {
            $since = date('Y-m-d H:i:s', strtotime('-7 days'));
        } else {
            $since = date('Y-m-d H:i:s', strtotime($since));
        }

        $serverTime = date('Y-m-d\TH:i:s');

        // 1. Pull Work Orders
        $woQuery = DB::table('t_work_order as a')
            ->leftJoin('s_users as b', 'a.ID_PENGAWAS', '=', 'b.id')
            ->leftJoin('m_pelabuhan as c', 'a.ID_PELABUHAN', '=', 'c.ID_PELABUHAN')
            ->leftJoin('m_client as d', 'a.ID_CLIENT', '=', 'd.ID_CLIENT')
            ->leftJoin('m_kapal as e', 'a.ID_KAPAL', '=', 'e.ID_KAPAL')
            ->leftJoin('m_dermaga as f', 'a.ID_DERMAGA', '=', 'f.ID_DERMAGA')
            ->leftJoin('m_aktifitas as g', 'a.ID_AKTIFITAS', '=', 'g.ID_AKTIFITAS')
            ->leftJoin('m_satuan as h', 'a.ID_SATUAN', '=', 'h.ID_SATUAN')
            ->whereNull('a.DELETED_AT')
            ->whereRaw("LOWER(a.STATUS) != 'hide'")
            ->where('a.UPDATED_AT', '>=', $since);

        if (!empty($pengawasId)) {
            $woQuery->where(function ($q) use ($pengawasId) {
                $q->whereNull('a.ID_PENGAWAS')->orWhere('a.ID_PENGAWAS', $pengawasId);
            });
        }

        $workorders = $woQuery->select(
            'a.ID_WORK_ORDER',
            'a.NO_WORK_ORDER',
            'a.STATUS',
            'a.ID_PELABUHAN',
            'a.ID_CLIENT',
            'a.ID_KAPAL',
            'a.ID_DERMAGA',
            'a.ID_AKTIFITAS',
            'a.ID_SATUAN',
            'a.ID_PENGAWAS',
            'a.VOLUME',
            'a.KETERANGAN',
            DB::raw("DATE_FORMAT(a.CREATED_AT, '%Y-%m-%dT%H:%i:%s') AS created_at"),
            DB::raw("DATE_FORMAT(a.UPDATED_AT, '%Y-%m-%dT%H:%i:%s') AS updated_at"),
            'b.username as pengawas_name',
            'c.NAMA as pelabuhan_name',
            'd.NAMA as client_name',
            'e.NAMA as kapal_name',
            'f.NAMA as dermaga_name',
            'g.NAMA as aktifitas_name',
            'h.NAMA as satuan_name',
            DB::raw("(SELECT COUNT(*) FROM t_work_order_detail wd WHERE wd.ID_WORK_ORDER = a.ID_WORK_ORDER AND wd.DELETED_AT IS NULL) AS total_laporan"),
            DB::raw("(SELECT GROUP_CONCAT(wa.NAMA_ALAT SEPARATOR ', ') FROM t_work_order_alat wa WHERE wa.ID_WORK_ORDER = a.ID_WORK_ORDER AND wa.DELETED_AT IS NULL) AS alat_name")
        )->orderBy('a.UPDATED_AT', 'DESC')->limit(200)->get();

        $woIds = $workorders->pluck('ID_WORK_ORDER')->toArray();

        // 2. Pull Details
        $details = [];
        if (!empty($woIds)) {
            $details = DB::table('t_work_order_detail as a')
                ->leftJoin('m_krk as b', 'a.ID_KRK', '=', 'b.ID_KRK')
                ->leftJoin('s_users as c', 'a.CREATED_BY', '=', 'c.id')
                ->whereIn('a.ID_WORK_ORDER', $woIds)
                ->whereNull('a.DELETED_AT')
                ->where('a.UPDATED_AT', '>=', $since)
                ->select('a.*', 'b.NAMA as NAMA_KRK', 'c.username as CREATED_USER')
                ->orderBy('a.CREATED_AT', 'DESC')
                ->get();
        }

        // 3. Pull Master Data
        $masterPelabuhan = DB::table('m_pelabuhan')->whereNull('DELETED_AT')->select('ID_PELABUHAN as id', 'NAMA as nama')->get();
        $masterKrk = DB::table('m_krk')->whereNull('DELETED_AT')->select('ID_KRK as id', 'NAMA as nama')->get();
        $masterSatuan = DB::table('m_satuan')->whereNull('DELETED_AT')->select('ID_SATUAN as id', 'NAMA as nama')->get();

        $formattedWo = $workorders->map(function ($row) {
            return [
                'id'             => (int) $row->ID_WORK_ORDER,
                'no_work_order'  => $row->NO_WORK_ORDER,
                'status'         => $row->STATUS,
                'pelabuhan_name' => $row->pelabuhan_name,
                'client_name'    => $row->client_name,
                'kapal_name'     => $row->kapal_name,
                'dermaga_name'   => $row->dermaga_name,
                'aktifitas_name' => $row->aktifitas_name,
                'satuan_name'    => $row->satuan_name,
                'pengawas_name'  => $row->pengawas_name,
                'alat_name'      => $row->alat_name,
                'volume'         => $row->VOLUME,
                'keterangan'     => $row->KETERANGAN,
                'total_laporan'  => (int) $row->total_laporan,
                'created_at'     => $row->created_at,
                'updated_at'     => $row->updated_at,
            ];
        });

        $formattedDetails = collect($details)->map(function ($row) {
            return [
                'id'          => (int) $row->ID_WORK_ORDER_DETAIL,
                'wo_id'       => (int) $row->ID_WORK_ORDER,
                'krk_id'      => (int) $row->ID_KRK,
                'krk_name'    => $row->NAMA_KRK,
                'hasil'       => floatval($row->HASIL),
                'ransum'      => isset($row->RANSUM) && $row->RANSUM !== null ? (int) $row->RANSUM : null,
                'catatan'     => $row->CATATAN,
                'foto_before' => !empty($row->FOTO_KEGIATAN_BEFORE) ? url($row->FOTO_KEGIATAN_BEFORE) : null,
                'foto_after'  => !empty($row->FOTO_KEGIATAN_AFTER) ? url($row->FOTO_KEGIATAN_AFTER) : null,
                'created_by'  => $row->CREATED_USER,
                'created_at'  => $row->CREATED_AT,
                'updated_at'  => $row->UPDATED_AT,
            ];
        });

        return $this->jsonSuccess('Data sync berhasil dimuat', [
            'server_time' => $serverTime,
            'workorders'  => $formattedWo,
            'reports'     => $formattedDetails,
            'master' => [
                'pelabuhan' => $masterPelabuhan,
                'krk'       => $masterKrk,
                'satuan'    => $masterSatuan,
            ],
        ]);
    }

    /**
     * POST /api/v1/sync/push
     */
    public function push(Request $request): JsonResponse
    {
        $woInputs = $request->input('workorders', []);
        $reports = $request->input('reports', []);

        if (empty($reports) && empty($woInputs)) {
            return $this->jsonError('Tidak ada data untuk disinkronkan.', 400);
        }

        $successCount = 0;
        $errorCount = 0;
        $results = [];
        $woResults = [];
        $woIdMap = [];

        try {
            DB::beginTransaction();

            // 1. Process Offline Created Work Orders
            foreach ($woInputs as $wIdx => $woIn) {
                $clientUuid = $woIn['client_uuid'] ?? $woIn['id'] ?? null;
                $clientId = $woIn['client_id'] ?? null;
                $kapalId = $woIn['kapal_id'] ?? null;
                $dermagaId = $woIn['dermaga_id'] ?? null;
                $aktifitasId = $woIn['aktifitas_id'] ?? null;
                $alatIds = $woIn['alat_ids'] ?? $woIn['alat_id'] ?? [];
                $tglWorkOrder = isset($woIn['tgl_work_order']) ? trim($woIn['tgl_work_order']) : date('Y-m-d');
                $inputNoWo = isset($woIn['no_work_order']) ? trim($woIn['no_work_order']) : '';
                $isManualNo = !empty($woIn['is_manual_no']);
                $volume = isset($woIn['volume']) && is_numeric($woIn['volume']) ? floatval($woIn['volume']) : null;
                $keterangan = isset($woIn['keterangan']) ? trim($woIn['keterangan']) : null;

                if (empty($clientId) || empty($kapalId) || empty($dermagaId)) {
                    $woResults[] = [
                        'index'       => $wIdx,
                        'client_uuid' => $clientUuid,
                        'status'      => 'error',
                        'message'     => 'Client, Kapal, dan Dermaga wajib diisi',
                    ];
                    $errorCount++;
                    continue;
                }

                $cleanTgl = str_replace('/', '-', $tglWorkOrder);
                $ts = strtotime($cleanTgl);
                $fTgl = $ts ? date('Y-m-d', $ts) : date('Y-m-d');

                if ($isManualNo && !empty($inputNoWo)) {
                    $noWo = $inputNoWo;
                } else {
                    $noWo = $this->pengaturanService->generateNoWorkOrder($fTgl);
                }

                $dermaga = DB::table('m_dermaga')->where('ID_DERMAGA', $dermagaId)->first();
                $pelabuhanId = $dermaga ? $dermaga->ID_PELABUHAN : null;

                $satuanId = null;
                if (!empty($aktifitasId)) {
                    $akt = DB::table('m_aktifitas')->where('ID_AKTIFITAS', $aktifitasId)->first();
                    $satuanId = $akt ? $akt->ID_SATUAN : null;
                }

                $cTime = $woIn['client_created_at'] ?? date('Y-m-d H:i:s');
                $userId = $this->userId($request);

                $newWoId = DB::table('t_work_order')->insertGetId([
                    'NO_WORK_ORDER' => $noWo,
                    'ID_CLIENT'     => (int) $clientId,
                    'ID_PELABUHAN'  => $pelabuhanId,
                    'ID_KAPAL'      => (int) $kapalId,
                    'ID_DERMAGA'    => (int) $dermagaId,
                    'ID_AKTIFITAS'  => !empty($aktifitasId) ? (int) $aktifitasId : null,
                    'ID_SATUAN'     => $satuanId,
                    'ID_PENGAWAS'   => $userId,
                    'VOLUME'        => $volume,
                    'KETERANGAN'    => $keterangan,
                    'STATUS'        => 'open',
                    'CREATED_BY'    => $userId,
                    'UPDATED_BY'    => $userId,
                    'CREATED_AT'    => $cTime,
                    'UPDATED_AT'    => date('Y-m-d H:i:s'),
                ]);

                if (!empty($alatIds) && is_array($alatIds)) {
                    $this->saveWoAlat($newWoId, $alatIds, $userId);
                }

                if (!empty($clientUuid)) {
                    $woIdMap[$clientUuid] = $newWoId;
                }
                if (isset($woIn['local_id'])) {
                    $woIdMap[$woIn['local_id']] = $newWoId;
                }

                $woResults[] = [
                    'index'         => $wIdx,
                    'client_uuid'   => $clientUuid,
                    'server_id'     => $newWoId,
                    'no_work_order' => $noWo,
                    'status'        => 'success',
                ];
                $successCount++;
            }

            // 2. Process Offline Created Reports
            foreach ($reports as $index => $report) {
                $targetWoId = $report['wo_id'] ?? null;
                if (isset($woIdMap[$targetWoId])) {
                    $targetWoId = $woIdMap[$targetWoId];
                }

                if (empty($targetWoId) || empty($report['krk_id']) || !isset($report['hasil'])) {
                    $results[] = [
                        'index'   => $index,
                        'status'  => 'error',
                        'message' => 'Field wo_id, krk_id, dan hasil wajib diisi',
                    ];
                    $errorCount++;
                    continue;
                }

                $workorder = DB::table('t_work_order')->where('ID_WORK_ORDER', $targetWoId)->first();
                if (!$workorder) {
                    $results[] = [
                        'index'   => $index,
                        'status'  => 'error',
                        'message' => 'Work Order ID ' . $targetWoId . ' tidak ditemukan',
                    ];
                    $errorCount++;
                    continue;
                }

                if (in_array(strtolower($workorder->STATUS), ['review', 'done'])) {
                    $results[] = [
                        'index'   => $index,
                        'status'  => 'error',
                        'message' => 'Work Order ' . $workorder->NO_WORK_ORDER . ' telah dikunci (status: ' . strtoupper($workorder->STATUS) . ')',
                    ];
                    $errorCount++;
                    continue;
                }

                $userId = $this->userId($request);
                if (!$this->isAdmin($request)) {
                    if (!empty($workorder->ID_PENGAWAS) && $workorder->ID_PENGAWAS != $userId) {
                        $results[] = [
                            'index'   => $index,
                            'status'  => 'error',
                            'message' => 'Anda tidak memiliki akses ke Work Order ID ' . $targetWoId,
                        ];
                        $errorCount++;
                        continue;
                    }
                }

                $krk = DB::table('m_krk')->where('ID_KRK', $report['krk_id'])->first();

                $newReportId = DB::table('t_work_order_detail')->insertGetId([
                    'ID_WORK_ORDER'        => (int) $targetWoId,
                    'ID_KRK'               => (int) $report['krk_id'],
                    'NAMA_KRK'             => $krk?->NAMA,
                    'HASIL'                => floatval($report['hasil']),
                    'RANSUM'               => isset($report['ransum']) && $report['ransum'] !== null && $report['ransum'] !== '' && is_numeric($report['ransum']) ? (int) $report['ransum'] : null,
                    'ID_SATUAN'            => $workorder->ID_SATUAN,
                    'CATATAN'              => $report['catatan'] ?? null,
                    'FOTO_KEGIATAN_BEFORE' => null,
                    'FOTO_KEGIATAN_AFTER'  => null,
                    'CREATED_BY'           => $userId,
                    'UPDATED_BY'           => $userId,
                    'CREATED_AT'           => $report['client_created_at'] ?? date('Y-m-d H:i:s'),
                    'UPDATED_AT'           => date('Y-m-d H:i:s'),
                ]);

                $woUpdate = [];
                if (strtolower($workorder->STATUS) === 'open') {
                    $woUpdate['STATUS'] = 'progress';
                }
                if (empty($workorder->ID_PENGAWAS)) {
                    $woUpdate['ID_PENGAWAS'] = $userId;
                }
                if (!empty($woUpdate)) {
                    $woUpdate['UPDATED_BY'] = $userId;
                    $woUpdate['UPDATED_AT'] = date('Y-m-d H:i:s');
                    DB::table('t_work_order')->where('ID_WORK_ORDER', $targetWoId)->update($woUpdate);
                }

                $results[] = [
                    'index'     => $index,
                    'status'    => 'success',
                    'server_id' => (int) $newReportId,
                    'wo_id'     => (int) $targetWoId,
                ];
                $successCount++;
            }

            DB::commit();

            return $this->jsonSuccess(
                "{$successCount} item berhasil disinkronkan" . ($errorCount > 0 ? ", {$errorCount} gagal" : ''),
                [
                    'success_count' => $successCount,
                    'error_count'   => $errorCount,
                    'workorders'    => $woResults,
                    'results'       => $results,
                    'server_time'   => date('Y-m-d\TH:i:s'),
                ]
            );
        } catch (Exception $e) {
            DB::rollBack();
            return $this->jsonError('Terjadi kesalahan sistem saat sinkronisasi: ' . $e->getMessage(), 500);
        }
    }

    protected function saveWoAlat(int $woId, array $alatIds, int $userId): void
    {
        $now = date('Y-m-d H:i:s');
        DB::table('t_work_order_alat')->where('ID_WORK_ORDER', $woId)->update(['DELETED_AT' => $now]);

        foreach ($alatIds as $alatId) {
            if (empty($alatId) || !is_numeric($alatId)) continue;
            $alat = DB::table('m_alat')->where('ID_ALAT', $alatId)->first();
            DB::table('t_work_order_alat')->insert([
                'ID_WORK_ORDER' => $woId,
                'ID_ALAT'       => $alatId,
                'NAMA_ALAT'     => $alat?->NAMA,
                'CREATED_BY'    => $userId,
                'UPDATED_BY'    => $userId,
                'CREATED_AT'    => $now,
                'UPDATED_AT'    => $now,
            ]);
        }
    }
}
