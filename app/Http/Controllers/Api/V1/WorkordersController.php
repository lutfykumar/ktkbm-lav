<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Services\PengaturanService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkordersController extends BaseApiController
{
    public function __construct(
        protected PengaturanService $pengaturanService
    ) {}

    /**
     * GET /api/v1/workorders
     */
    public function index(Request $request): JsonResponse
    {
        if ($request->isMethod('post')) {
            return $this->create($request);
        }

        $status = $request->input('status');
        $date = $request->input('date');
        $pelabuhan = $request->input('pelabuhan');
        $page = max(1, (int) $request->input('page', 1));
        $limit = min(50, max(1, (int) $request->input('limit', 20)));
        $offset = ($page - 1) * $limit;

        $pengawasId = $this->isAdmin($request) ? null : $this->userId($request);

        $query = DB::table('t_work_order as a')
            ->whereNull('a.DELETED_AT')
            ->whereRaw("LOWER(a.STATUS) != 'hide'");

        if (!empty($status)) {
            $query->where('a.STATUS', $status);
        }
        if (!empty($date)) {
            $query->whereDate('a.CREATED_AT', $date);
        }
        if (!empty($pelabuhan)) {
            $query->where('a.ID_PELABUHAN', $pelabuhan);
        }
        if (!empty($pengawasId)) {
            $query->where(function ($q) use ($pengawasId) {
                $q->whereNull('a.ID_PENGAWAS')->orWhere('a.ID_PENGAWAS', $pengawasId);
            });
        }

        $total = $query->count();

        $rows = (clone $query)
            ->leftJoin('s_users as b', 'a.ID_PENGAWAS', '=', 'b.id')
            ->leftJoin('m_pelabuhan as c', 'a.ID_PELABUHAN', '=', 'c.ID_PELABUHAN')
            ->leftJoin('m_client as d', 'a.ID_CLIENT', '=', 'd.ID_CLIENT')
            ->leftJoin('m_kapal as e', 'a.ID_KAPAL', '=', 'e.ID_KAPAL')
            ->leftJoin('m_dermaga as f', 'a.ID_DERMAGA', '=', 'f.ID_DERMAGA')
            ->leftJoin('m_aktifitas as g', 'a.ID_AKTIFITAS', '=', 'g.ID_AKTIFITAS')
            ->leftJoin('m_satuan as h', 'a.ID_SATUAN', '=', 'h.ID_SATUAN')
            ->select(
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
                DB::raw("DATE_FORMAT(a.CREATED_AT, '%Y-%m-%d %H:%i:%s') AS created_at"),
                DB::raw("DATE_FORMAT(a.UPDATED_AT, '%Y-%m-%d %H:%i:%s') AS updated_at"),
                'b.username as pengawas_name',
                'c.NAMA as pelabuhan_name',
                'd.NAMA as client_name',
                'e.NAMA as kapal_name',
                'f.NAMA as dermaga_name',
                'g.NAMA as aktifitas_name',
                'h.NAMA as satuan_name',
                DB::raw("(SELECT COUNT(*) FROM t_work_order_detail wd WHERE wd.ID_WORK_ORDER = a.ID_WORK_ORDER AND wd.DELETED_AT IS NULL) AS total_laporan"),
                DB::raw("(SELECT GROUP_CONCAT(wa.NAMA_ALAT SEPARATOR ', ') FROM t_work_order_alat wa WHERE wa.ID_WORK_ORDER = a.ID_WORK_ORDER AND wa.DELETED_AT IS NULL) AS alat_name")
            )
            ->orderBy('a.UPDATED_AT', 'DESC')
            ->limit($limit)
            ->offset($offset)
            ->get();

        $workorders = $rows->map(function ($row) {
            return $this->formatWorkorder($row);
        });

        return $this->jsonSuccess('Data work order berhasil dimuat', $workorders, 200, [
            'page'       => $page,
            'limit'      => $limit,
            'total'      => $total,
            'total_page' => (int) ceil($total / $limit),
        ]);
    }

    /**
     * GET /api/v1/workorders/{id}
     */
    public function detail(Request $request, $id = null): JsonResponse
    {
        if ($request->isMethod('put') || $request->isMethod('patch')) {
            return $this->update($request, $id);
        }

        if (empty($id) || !is_numeric($id)) {
            return $this->jsonError('ID Work Order tidak valid.', 400);
        }

        $workorder = $this->getWorkorderById((int) $id);

        if (!$workorder) {
            return $this->jsonError('Work Order tidak ditemukan.', 404);
        }

        if (!$this->isAdmin($request)) {
            if (!empty($workorder->ID_PENGAWAS) && $workorder->ID_PENGAWAS != $this->userId($request)) {
                return $this->jsonError('Anda tidak memiliki akses ke Work Order ini.', 403);
            }
        }

        $details = $this->getWorkorderDetails((int) $id);
        $alat = $this->getWorkorderAlat((int) $id);

        $data = [
            'id'            => (int) $workorder->ID_WORK_ORDER,
            'no_work_order' => $workorder->NO_WORK_ORDER,
            'status'        => $workorder->STATUS,
            'volume'        => $workorder->VOLUME,
            'keterangan'    => $workorder->KETERANGAN,
            'pelabuhan'     => ['id' => (int) $workorder->ID_PELABUHAN, 'nama' => $workorder->NAMA_PELABUHAN],
            'client'        => ['id' => (int) $workorder->ID_CLIENT, 'nama' => $workorder->NAMA_CLIENT],
            'kapal'         => ['id' => (int) $workorder->ID_KAPAL, 'nama' => $workorder->NAMA_KAPAL],
            'dermaga'       => ['id' => !empty($workorder->ID_DERMAGA) ? (int) $workorder->ID_DERMAGA : null, 'nama' => $workorder->NAMA_DERMAGA],
            'aktifitas'     => ['id' => (int) $workorder->ID_AKTIFITAS, 'nama' => $workorder->NAMA_AKTIFITAS],
            'satuan'        => ['id' => !empty($workorder->ID_SATUAN) ? (int) $workorder->ID_SATUAN : null, 'nama' => $workorder->NAMA_SATUAN],
            'pengawas'      => ['id' => !empty($workorder->ID_PENGAWAS) ? (int) $workorder->ID_PENGAWAS : null, 'nama' => $workorder->PENGAWAS_USER],
            'alat'          => $workorder->NAMA_ALAT,
            'created_by'    => $workorder->CREATED_USER,
            'updated_by'    => $workorder->UPDATED_USER,
            'created_at'    => $workorder->CREATED_AT,
            'updated_at'    => $workorder->UPDATED_AT,
            'alat_list'     => $alat->map(fn($a) => ['id' => (int) $a->ID_ALAT, 'nama' => $a->NAMA]),
            'reports'       => $details->map(fn($d) => $this->formatReport($d)),
            'total_laporan' => count($details),
        ];

        return $this->jsonSuccess('Detail work order berhasil dimuat', $data);
    }

    /**
     * POST /api/v1/workorders (or /api/v1/workorders/create)
     */
    public function create(Request $request): JsonResponse
    {
        $clientId = $request->input('client_id');
        $kapalId = $request->input('kapal_id');
        $dermagaId = $request->input('dermaga_id');
        $aktifitasId = $request->input('aktifitas_id');
        $alatIds = $request->input('alat_ids') ?: $request->input('alat_id', []);
        $tglWorkOrder = trim($request->input('tgl_work_order', ''));
        $inputNoWorkOrder = trim($request->input('no_work_order', ''));
        $isManualNo = !empty($request->input('is_manual_no'));
        $keterangan = trim($request->input('keterangan', ''));
        $volume = $request->input('volume') !== null && is_numeric($request->input('volume')) ? floatval($request->input('volume')) : null;
        $clientUuid = trim($request->input('client_uuid', ''));

        if (empty($clientId) || !is_numeric($clientId)) {
            return $this->jsonError('Client wajib dipilih.', 422);
        }
        if (empty($kapalId) || !is_numeric($kapalId)) {
            return $this->jsonError('Kapal wajib dipilih.', 422);
        }
        if (empty($dermagaId) || !is_numeric($dermagaId)) {
            return $this->jsonError('Dermaga wajib dipilih.', 422);
        }

        if (!empty($tglWorkOrder)) {
            $cleanTgl = str_replace('/', '-', $tglWorkOrder);
            $ts = strtotime($cleanTgl);
            $tglWorkOrder = $ts ? date('Y-m-d', $ts) : date('Y-m-d');
        } else {
            $tglWorkOrder = date('Y-m-d');
        }

        if ($isManualNo && !empty($inputNoWorkOrder)) {
            $check = DB::table('t_work_order')->where('NO_WORK_ORDER', $inputNoWorkOrder)->whereNull('DELETED_AT')->first();
            if ($check) {
                return $this->jsonError('Nomor Work Order sudah digunakan.', 422);
            }
            $noWorkOrder = $inputNoWorkOrder;
        } else {
            $noWorkOrder = $this->pengaturanService->generateNoWorkOrder($tglWorkOrder);
        }

        try {
            DB::beginTransaction();

            $dermaga = DB::table('m_dermaga')->where('ID_DERMAGA', $dermagaId)->first();
            $pelabuhanId = $dermaga ? $dermaga->ID_PELABUHAN : null;

            $satuanId = null;
            if (!empty($aktifitasId) && is_numeric($aktifitasId)) {
                $aktifitas = DB::table('m_aktifitas')->where('ID_AKTIFITAS', $aktifitasId)->first();
                $satuanId = $aktifitas ? $aktifitas->ID_SATUAN : null;
            }

            $userId = $this->userId($request);
            $createdAt = date('Y-m-d H:i:s', strtotime($tglWorkOrder . ' ' . date('H:i:s')));
            $now = date('Y-m-d H:i:s');

            $newId = DB::table('t_work_order')->insertGetId([
                'NO_WORK_ORDER' => $noWorkOrder,
                'ID_CLIENT'     => (int) $clientId,
                'ID_PELABUHAN'  => $pelabuhanId,
                'ID_KAPAL'      => (int) $kapalId,
                'ID_DERMAGA'    => (int) $dermagaId,
                'ID_AKTIFITAS'  => !empty($aktifitasId) ? (int) $aktifitasId : null,
                'ID_SATUAN'     => $satuanId,
                'ID_PENGAWAS'   => $userId,
                'VOLUME'        => $volume,
                'KETERANGAN'    => !empty($keterangan) ? $keterangan : null,
                'STATUS'        => 'open',
                'CREATED_BY'    => $userId,
                'UPDATED_BY'    => $userId,
                'CREATED_AT'    => $createdAt,
                'UPDATED_AT'    => $now,
            ]);

            if (!empty($alatIds) && is_array($alatIds)) {
                $this->saveWoAlat($newId, $alatIds, $userId);
            }

            DB::commit();

            $createdWo = $this->getWorkorderById($newId);

            return $this->jsonSuccess('Work Order berhasil dibuat', [
                'id'             => $newId,
                'client_uuid'    => !empty($clientUuid) ? $clientUuid : null,
                'no_work_order'  => $noWorkOrder,
                'status'         => 'open',
                'created_at'     => $createdAt,
                'kapal_name'     => $createdWo?->NAMA_KAPAL,
                'client_name'    => $createdWo?->NAMA_CLIENT,
                'dermaga_name'   => $createdWo?->NAMA_DERMAGA,
                'pelabuhan_name' => $createdWo?->NAMA_PELABUHAN,
                'aktifitas_name' => $createdWo?->NAMA_AKTIFITAS,
                'satuan_name'    => $createdWo?->NAMA_SATUAN,
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->jsonError('Terjadi kesalahan sistem saat membuat Work Order: ' . $e->getMessage(), 500);
        }
    }

    /**
     * PUT/POST /api/v1/workorders/{id}/update (or PUT /api/v1/workorders/{id})
     */
    public function update(Request $request, $id = null): JsonResponse
    {
        if (empty($id) || !is_numeric($id)) {
            return $this->jsonError('ID Work Order tidak valid.', 400);
        }

        $workorder = DB::table('t_work_order')->where('ID_WORK_ORDER', $id)->first();
        if (!$workorder) {
            return $this->jsonError('Work Order tidak ditemukan.', 404);
        }

        if (!$this->isAdmin($request)) {
            if (!empty($workorder->ID_PENGAWAS) && $workorder->ID_PENGAWAS != $this->userId($request)) {
                return $this->jsonError('Anda tidak memiliki akses ke Work Order ini.', 403);
            }

            if (in_array(strtolower($workorder->STATUS), ['review', 'done'])) {
                return $this->jsonError('Work Order sedang dalam proses review atau sudah selesai dan tidak dapat diedit.', 403);
            }
        }

        $updateData = [
            'UPDATED_BY' => $this->userId($request),
            'UPDATED_AT' => date('Y-m-d H:i:s'),
        ];

        if ($request->has('client_id') && is_numeric($request->input('client_id'))) {
            $updateData['ID_CLIENT'] = (int) $request->input('client_id');
        }
        if ($request->has('kapal_id') && is_numeric($request->input('kapal_id'))) {
            $updateData['ID_KAPAL'] = (int) $request->input('kapal_id');
        }
        if ($request->has('dermaga_id') && is_numeric($request->input('dermaga_id'))) {
            $updateData['ID_DERMAGA'] = (int) $request->input('dermaga_id');
            $dermaga = DB::table('m_dermaga')->where('ID_DERMAGA', $request->input('dermaga_id'))->first();
            if ($dermaga) {
                $updateData['ID_PELABUHAN'] = $dermaga->ID_PELABUHAN;
            }
        }
        if ($request->has('aktifitas_id')) {
            $aktId = $request->input('aktifitas_id');
            if (empty($aktId)) {
                $updateData['ID_AKTIFITAS'] = null;
                $updateData['ID_SATUAN'] = null;
            } elseif (is_numeric($aktId)) {
                $updateData['ID_AKTIFITAS'] = (int) $aktId;
                $akt = DB::table('m_aktifitas')->where('ID_AKTIFITAS', $aktId)->first();
                if ($akt) {
                    $updateData['ID_SATUAN'] = $akt->ID_SATUAN;
                }
            }
        }
        if ($request->has('volume')) {
            $vol = $request->input('volume');
            $updateData['VOLUME'] = is_numeric($vol) ? floatval($vol) : null;
        }
        if ($request->has('keterangan')) {
            $updateData['KETERANGAN'] = trim($request->input('keterangan'));
        }

        try {
            DB::beginTransaction();

            DB::table('t_work_order')->where('ID_WORK_ORDER', $id)->update($updateData);

            $alatIds = $request->input('alat_ids') ?: $request->input('alat_id');
            if (is_array($alatIds)) {
                $this->saveWoAlat((int) $id, $alatIds, $this->userId($request));
            }

            DB::commit();

            $updatedWo = $this->getWorkorderById((int) $id);

            return $this->jsonSuccess('Work Order berhasil diperbarui', [
                'id'             => (int) $id,
                'no_work_order'  => $updatedWo?->NO_WORK_ORDER,
                'status'         => $updatedWo?->STATUS,
                'kapal_name'     => $updatedWo?->NAMA_KAPAL,
                'client_name'    => $updatedWo?->NAMA_CLIENT,
                'dermaga_name'   => $updatedWo?->NAMA_DERMAGA,
                'pelabuhan_name' => $updatedWo?->NAMA_PELABUHAN,
                'aktifitas_name' => $updatedWo?->NAMA_AKTIFITAS,
                'satuan_name'    => $updatedWo?->NAMA_SATUAN,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->jsonError('Terjadi kesalahan sistem saat memperbarui Work Order: ' . $e->getMessage(), 500);
        }
    }

    /**
     * PUT/POST /api/v1/workorders/{id}/status
     */
    public function update_status(Request $request, $id = null): JsonResponse
    {
        if (empty($id) || !is_numeric($id)) {
            return $this->jsonError('ID Work Order tidak valid.', 400);
        }

        $newStatus = strtolower($request->input('status', ''));
        $allowedStatuses = ['open', 'progress', 'review', 'done'];

        if (!in_array($newStatus, $allowedStatuses)) {
            return $this->jsonError('Status tidak valid. Gunakan: ' . implode(', ', $allowedStatuses), 422);
        }

        $workorder = DB::table('t_work_order')->where('ID_WORK_ORDER', $id)->first();
        if (!$workorder) {
            return $this->jsonError('Work Order tidak ditemukan.', 404);
        }

        if (!$this->isAdmin($request)) {
            if (!empty($workorder->ID_PENGAWAS) && $workorder->ID_PENGAWAS != $this->userId($request)) {
                return $this->jsonError('Anda tidak memiliki akses ke Work Order ini.', 403);
            }

            if ($newStatus === 'done') {
                return $this->jsonError('Hanya Admin Kantor yang dapat mengunci status Work Order ke DONE.', 403);
            }

            if (in_array(strtolower($workorder->STATUS), ['review', 'done'])) {
                return $this->jsonError('Work Order sedang dalam proses review atau sudah selesai dan tidak dapat diubah oleh pengawas.', 403);
            }
        }

        DB::table('t_work_order')->where('ID_WORK_ORDER', $id)->update([
            'STATUS'     => $newStatus,
            'UPDATED_BY' => $this->userId($request),
            'UPDATED_AT' => date('Y-m-d H:i:s'),
        ]);

        return $this->jsonSuccess(
            'Status Work Order ' . $workorder->NO_WORK_ORDER . ' berhasil diubah menjadi ' . strtoupper($newStatus),
            ['id' => (int) $id, 'status' => $newStatus]
        );
    }

    /**
     * POST /api/v1/workorders/{id}/submit-review
     */
    public function submit_review(Request $request, $id = null): JsonResponse
    {
        if (empty($id) || !is_numeric($id)) {
            return $this->jsonError('ID Work Order tidak valid.', 400);
        }

        $workorder = DB::table('t_work_order')->where('ID_WORK_ORDER', $id)->first();
        if (!$workorder) {
            return $this->jsonError('Work Order tidak ditemukan.', 404);
        }

        if (!$this->isAdmin($request)) {
            if (!empty($workorder->ID_PENGAWAS) && $workorder->ID_PENGAWAS != $this->userId($request)) {
                return $this->jsonError('Anda tidak memiliki akses ke Work Order ini.', 403);
            }
        }

        if (in_array(strtolower($workorder->STATUS), ['review', 'done'])) {
            return $this->jsonError('Work Order sudah dalam status review atau telah selesai.', 400);
        }

        $reportCount = DB::table('t_work_order_detail')
            ->where('ID_WORK_ORDER', $id)
            ->whereNull('DELETED_AT')
            ->count();

        if ($reportCount === 0) {
            return $this->jsonError('Tidak dapat mengirim review: Belum ada laporan aktivitas lapangan yang diinput.', 422);
        }

        DB::table('t_work_order')->where('ID_WORK_ORDER', $id)->update([
            'STATUS'     => 'review',
            'UPDATED_BY' => $this->userId($request),
            'UPDATED_AT' => date('Y-m-d H:i:s'),
        ]);

        return $this->jsonSuccess('Work Order ' . $workorder->NO_WORK_ORDER . ' berhasil dikirim untuk review oleh Admin Kantor', [
            'id'     => (int) $id,
            'status' => 'review',
        ]);
    }

    /**
     * GET/POST /api/v1/workorders/{id}/reports
     */
    public function reports(Request $request, $id = null): JsonResponse
    {
        if ($request->isMethod('get')) {
            return $this->listReports($request, $id);
        } elseif ($request->isMethod('post')) {
            return $this->storeReport($request, $id);
        }

        return $this->jsonError('Method not allowed.', 405);
    }

    protected function listReports(Request $request, $id): JsonResponse
    {
        if (empty($id) || !is_numeric($id)) {
            return $this->jsonError('ID Work Order tidak valid.', 400);
        }

        $workorder = DB::table('t_work_order')->where('ID_WORK_ORDER', $id)->first();
        if (!$workorder) {
            return $this->jsonError('Work Order tidak ditemukan.', 404);
        }

        $details = $this->getWorkorderDetails((int) $id);
        $reports = $details->map(fn($d) => $this->formatReport($d));

        return $this->jsonSuccess('Data laporan berhasil dimuat', $reports);
    }

    protected function storeReport(Request $request, $id): JsonResponse
    {
        if (empty($id) || !is_numeric($id)) {
            return $this->jsonError('ID Work Order tidak valid.', 400);
        }

        $workorder = DB::table('t_work_order')->where('ID_WORK_ORDER', $id)->first();
        if (!$workorder) {
            return $this->jsonError('Work Order tidak ditemukan.', 404);
        }

        if (!$this->isAdmin($request)) {
            if (!empty($workorder->ID_PENGAWAS) && $workorder->ID_PENGAWAS != $this->userId($request)) {
                return $this->jsonError('Anda tidak memiliki akses ke Work Order ini.', 403);
            }
        }

        if (in_array(strtolower($workorder->STATUS), ['review', 'done'])) {
            return $this->jsonError('Tidak dapat menambahkan laporan. Work Order sedang dalam proses review atau sudah selesai.', 403);
        }

        $krkId = $request->input('krk_id');
        $hasil = $request->input('hasil');
        if (is_string($hasil)) {
            $hasil = str_replace(',', '.', trim($hasil));
        }
        $ransum = $request->input('ransum');
        if (is_string($ransum)) {
            $ransum = trim($ransum);
        }
        $catatan = $request->input('catatan');

        if (empty($krkId) || !is_numeric($krkId)) {
            return $this->jsonError('KRK ID wajib diisi.', 422);
        }
        if ($hasil === null || $hasil === '' || !is_numeric($hasil)) {
            return $this->jsonError('Hasil wajib diisi dan berupa angka.', 422);
        }

        $fotoBefore = $this->uploadPhoto($request, 'foto_before');
        $fotoAfter = $this->uploadPhoto($request, 'foto_after');

        $krk = DB::table('m_krk')->where('ID_KRK', $krkId)->first();

        try {
            DB::beginTransaction();

            $userId = $this->userId($request);
            $now = date('Y-m-d H:i:s');

            $reportId = DB::table('t_work_order_detail')->insertGetId([
                'ID_WORK_ORDER'        => (int) $id,
                'ID_KRK'               => (int) $krkId,
                'NAMA_KRK'             => $krk?->NAMA,
                'HASIL'                => floatval($hasil),
                'RANSUM'               => ($ransum !== null && $ransum !== '' && is_numeric($ransum)) ? (int) $ransum : null,
                'ID_SATUAN'            => $workorder->ID_SATUAN,
                'CATATAN'              => $catatan,
                'FOTO_KEGIATAN_BEFORE' => $fotoBefore ?: null,
                'FOTO_KEGIATAN_AFTER'  => $fotoAfter ?: null,
                'CREATED_BY'           => $userId,
                'UPDATED_BY'           => $userId,
                'CREATED_AT'           => $now,
                'UPDATED_AT'           => $now,
            ]);

            // Auto-update WO status to progress if open, assign pengawas if empty
            $woUpdate = [];
            if (strtolower($workorder->STATUS) === 'open') {
                $woUpdate['STATUS'] = 'progress';
            }
            if (empty($workorder->ID_PENGAWAS)) {
                $woUpdate['ID_PENGAWAS'] = $userId;
            }
            if (!empty($woUpdate)) {
                $woUpdate['UPDATED_BY'] = $userId;
                $woUpdate['UPDATED_AT'] = $now;
                DB::table('t_work_order')->where('ID_WORK_ORDER', $id)->update($woUpdate);
            }

            DB::commit();

            return $this->jsonSuccess('Laporan berhasil disimpan', [
                'id'          => (int) $reportId,
                'wo_id'       => (int) $id,
                'krk_id'      => (int) $krkId,
                'hasil'       => floatval($hasil),
                'ransum'      => ($ransum !== null && $ransum !== '' && is_numeric($ransum)) ? (int) $ransum : null,
                'foto_before' => $fotoBefore ? url($fotoBefore) : null,
                'foto_after'  => $fotoAfter ? url($fotoAfter) : null,
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->jsonError('Terjadi kesalahan sistem saat menyimpan laporan: ' . $e->getMessage(), 500);
        }
    }

    /**
     * DELETE/POST /api/v1/reports/{id}
     */
    public function delete_report(Request $request, $id = null): JsonResponse
    {
        if (empty($id) || !is_numeric($id)) {
            return $this->jsonError('ID laporan tidak valid.', 400);
        }

        $detail = DB::table('t_work_order_detail')->where('ID_WORK_ORDER_DETAIL', $id)->whereNull('DELETED_AT')->first();
        if (!$detail) {
            return $this->jsonError('Laporan tidak ditemukan.', 404);
        }

        $workorder = DB::table('t_work_order')->where('ID_WORK_ORDER', $detail->ID_WORK_ORDER)->whereNull('DELETED_AT')->first();
        if (!$workorder) {
            return $this->jsonError('Work Order tidak ditemukan.', 404);
        }

        // Access check: Admin, or report creator, or assigned pengawas of the WO
        if (!$this->isAdmin($request)) {
            $userId = $this->userId($request);
            $isCreator = ($detail->CREATED_BY == $userId);
            $isWoPengawas = (!empty($workorder->ID_PENGAWAS) && $workorder->ID_PENGAWAS == $userId);
            if (!$isCreator && !$isWoPengawas) {
                return $this->jsonError('Anda tidak memiliki akses untuk menghapus laporan ini.', 403);
            }

            // Check if WO is locked
            if (in_array(strtolower($workorder->STATUS), ['review', 'done'])) {
                return $this->jsonError('Tidak dapat menghapus laporan. Work Order sedang dalam proses review atau sudah selesai.', 403);
            }
        }

        DB::table('t_work_order_detail')->where('ID_WORK_ORDER_DETAIL', $id)->update([
            'DELETED_AT' => date('Y-m-d H:i:s'),
            'UPDATED_BY' => $this->userId($request),
            'UPDATED_AT' => date('Y-m-d H:i:s'),
        ]);

        return $this->jsonSuccess('Laporan berhasil dihapus');
    }

    /**
     * POST/PUT/PATCH /api/v1/reports/{id}/update (or /api/v1/workorders/{id}/reports/{report_id}/update)
     * Update detail report (KRK, hasil, ransum, catatan, optional photos)
     * Allowed while WO status is open/progress
     */
    public function update_report(Request $request, $id = null, $report_id = null): JsonResponse
    {
        $targetReportId = $report_id ?? $id;

        if (empty($targetReportId) || !is_numeric($targetReportId)) {
            return $this->jsonError('ID laporan tidak valid.', 400);
        }

        $detail = DB::table('t_work_order_detail')->where('ID_WORK_ORDER_DETAIL', $targetReportId)->whereNull('DELETED_AT')->first();
        if (!$detail) {
            return $this->jsonError('Laporan tidak ditemukan.', 404);
        }

        $workorder = DB::table('t_work_order')->where('ID_WORK_ORDER', $detail->ID_WORK_ORDER)->whereNull('DELETED_AT')->first();
        if (!$workorder) {
            return $this->jsonError('Work Order tidak ditemukan.', 404);
        }

        // Access check: Admin, or report creator, or assigned pengawas of the WO
        if (!$this->isAdmin($request)) {
            $userId = $this->userId($request);
            $isCreator = ($detail->CREATED_BY == $userId);
            $isWoPengawas = (!empty($workorder->ID_PENGAWAS) && $workorder->ID_PENGAWAS == $userId);
            if (!$isCreator && !$isWoPengawas) {
                return $this->jsonError('Anda tidak memiliki akses untuk mengedit laporan ini.', 403);
            }

            // Check if WO is locked
            if (in_array(strtolower($workorder->STATUS), ['review', 'done'])) {
                return $this->jsonError('Tidak dapat mengedit laporan. Work Order sedang dalam proses review atau sudah selesai.', 403);
            }
        }

        $krkId  = $request->input('krk_id', $detail->ID_KRK);
        $hasil   = $request->input('hasil', $detail->HASIL);
        if (is_string($hasil)) {
            $hasil = str_replace(',', '.', trim($hasil));
        }
        $ransum  = $request->input('ransum', $detail->RANSUM);
        if (is_string($ransum)) {
            $ransum = trim($ransum);
        }
        $catatan = $request->input('catatan', $detail->CATATAN);

        if (empty($krkId) || !is_numeric($krkId)) {
            return $this->jsonError('KRK ID wajib diisi.', 422);
        }

        if ($hasil === null || $hasil === '' || !is_numeric($hasil)) {
            return $this->jsonError('Hasil wajib diisi dan berupa angka.', 422);
        }

        // Handle photo uploads if any provided
        $fotoBefore = $this->uploadPhoto($request, 'foto_before');
        $fotoAfter  = $this->uploadPhoto($request, 'foto_after');

        $krk = DB::table('m_krk')->where('ID_KRK', $krkId)->first();

        try {
            DB::beginTransaction();

            $data = [
                'ID_KRK'     => (int) $krkId,
                'NAMA_KRK'   => $krk ? $krk->NAMA : $detail->NAMA_KRK,
                'HASIL'      => floatval($hasil),
                'RANSUM'     => ($ransum !== null && $ransum !== '' && is_numeric($ransum)) ? (int) $ransum : null,
                'CATATAN'    => $catatan,
                'UPDATED_BY' => $this->userId($request),
                'UPDATED_AT' => date('Y-m-d H:i:s'),
            ];

            if (!empty($fotoBefore)) {
                $data['FOTO_KEGIATAN_BEFORE'] = $fotoBefore;
            }
            if (!empty($fotoAfter)) {
                $data['FOTO_KEGIATAN_AFTER'] = $fotoAfter;
            }

            DB::table('t_work_order_detail')->where('ID_WORK_ORDER_DETAIL', $targetReportId)->update($data);

            DB::commit();

            $updated = DB::table('t_work_order_detail')->where('ID_WORK_ORDER_DETAIL', $targetReportId)->first();

            return $this->jsonSuccess('Laporan berhasil diperbarui', $this->formatReport($updated));
        } catch (Exception $e) {
            DB::rollBack();
            return $this->jsonError('Terjadi kesalahan sistem saat memperbarui laporan: ' . $e->getMessage(), 500);
        }
    }

    /**
     * POST /api/v1/reports/{id}/photos
     */
    public function upload_photos(Request $request, $id = null): JsonResponse
    {
        if (empty($id) || !is_numeric($id)) {
            return $this->jsonError('ID laporan tidak valid.', 400);
        }

        $detail = DB::table('t_work_order_detail')->where('ID_WORK_ORDER_DETAIL', $id)->whereNull('DELETED_AT')->first();
        if (!$detail) {
            return $this->jsonError('Laporan tidak ditemukan.', 404);
        }

        if (!$this->isAdmin($request) && $detail->CREATED_BY != $this->userId($request)) {
            return $this->jsonError('Anda tidak memiliki akses untuk mengupload foto laporan ini.', 403);
        }

        $fotoBefore = $this->uploadPhoto($request, 'foto_before');
        $fotoAfter = $this->uploadPhoto($request, 'foto_after');

        if (empty($fotoBefore) && empty($fotoAfter)) {
            return $this->jsonError('Tidak ada foto yang diupload.', 422);
        }

        $update = [
            'UPDATED_BY' => $this->userId($request),
            'UPDATED_AT' => date('Y-m-d H:i:s'),
        ];
        if (!empty($fotoBefore)) {
            $update['FOTO_KEGIATAN_BEFORE'] = $fotoBefore;
        }
        if (!empty($fotoAfter)) {
            $update['FOTO_KEGIATAN_AFTER'] = $fotoAfter;
        }

        DB::table('t_work_order_detail')->where('ID_WORK_ORDER_DETAIL', $id)->update($update);

        return $this->jsonSuccess('Foto laporan berhasil diupload', [
            'id'          => (int) $id,
            'foto_before' => !empty($fotoBefore) ? url($fotoBefore) : (!empty($detail->FOTO_KEGIATAN_BEFORE) ? url($detail->FOTO_KEGIATAN_BEFORE) : null),
            'foto_after'  => !empty($fotoAfter) ? url($fotoAfter) : (!empty($detail->FOTO_KEGIATAN_AFTER) ? url($detail->FOTO_KEGIATAN_AFTER) : null),
        ]);
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    protected function uploadPhoto(Request $request, string $fieldName): ?string
    {
        if (!$request->hasFile($fieldName)) {
            return null;
        }

        $file = $request->file($fieldName);
        if (!$file->isValid()) {
            return null;
        }

        $year = date('Y');
        $month = date('m');
        $uploadDir = public_path("filemanager/activity/{$year}/{$month}");

        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $ext = $file->getClientOriginalExtension() ?: ($file->guessExtension() ?: 'jpg');
        $filename = md5(uniqid(rand(), true)) . '.' . $ext;
        $file->move($uploadDir, $filename);

        return "filemanager/activity/{$year}/{$month}/{$filename}";
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

    protected function getWorkorderById(int $id): ?object
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
            ->select(
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
                DB::raw("(SELECT GROUP_CONCAT(wa.NAMA_ALAT SEPARATOR ', ') FROM t_work_order_alat wa WHERE wa.ID_WORK_ORDER = a.ID_WORK_ORDER AND wa.DELETED_AT IS NULL) AS NAMA_ALAT")
            )
            ->where('a.ID_WORK_ORDER', $id)
            ->first();
    }

    protected function getWorkorderDetails(int $id)
    {
        return DB::table('t_work_order_detail as a')
            ->leftJoin('m_krk as b', 'a.ID_KRK', '=', 'b.ID_KRK')
            ->leftJoin('s_users as c', 'a.CREATED_BY', '=', 'c.id')
            ->select('a.*', 'b.NAMA as NAMA_KRK', 'c.username as CREATED_USER')
            ->where('a.ID_WORK_ORDER', $id)
            ->whereNull('a.DELETED_AT')
            ->orderBy('a.CREATED_AT', 'DESC')
            ->get();
    }

    protected function getWorkorderAlat(int $id)
    {
        return DB::table('t_work_order_alat as a')
            ->leftJoin('m_alat as b', 'a.ID_ALAT', '=', 'b.ID_ALAT')
            ->select('a.ID_ALAT', 'b.NAMA')
            ->where('a.ID_WORK_ORDER', $id)
            ->whereNull('a.DELETED_AT')
            ->get();
    }

    protected function formatWorkorder(object $row): array
    {
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
            'total_laporan'  => (int) $row->total_laporan,
            'created_at'     => $row->created_at,
            'updated_at'     => $row->updated_at,
        ];
    }

    protected function formatReport(object $row): array
    {
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
            'created_by'  => $row->CREATED_USER ?? null,
            'created_at'  => $row->CREATED_AT,
            'updated_at'  => $row->UPDATED_AT,
        ];
    }
}
