<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ActivityController extends Controller
{
    protected string $slug = 'tasks-aktifitas-lapangan';
    protected string $permissionRead;
    protected string $permissionCreate;
    protected string $permissionUpdate;
    protected string $permissionDelete;

    public function __construct()
    {
        $this->permissionRead   = 'read-' . $this->slug;
        $this->permissionCreate = 'create-' . $this->slug;
        $this->permissionUpdate = 'update-' . $this->slug;
        $this->permissionDelete = 'delete-' . $this->slug;
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

        ActivityLogService::log('Lihat List Aktifitas Lapangan', 'view', 'activity');

        return view('activity.index', [
            'title' => 'Aktifitas Lapangan',
            'title_header' => 'Aktifitas Lapangan',
            'title_parent' => 'Tasks',
            'permission_create' => $this->hasPerm($this->permissionCreate) ? 1 : 0,
        ]);
    }

    public function result(Request $request)
    {
        if (!$this->hasPerm($this->permissionRead)) {
            return response('<div class="alert alert-danger">Anda tidak memiliki akses</div>', 403);
        }

        $tgl = $request->input('tgl', date('Y-m-d'));
        $pelabuhan = $request->input('pelabuhan');

        $user = auth()->user();
        $pengawasId = in_array((int)$user->group_id, [1, 2]) ? null : $user->id;

        $open = $this->getPengawasWorkorder($tgl, $pelabuhan, 'open', $pengawasId);
        $progress = $this->getPengawasWorkorder($tgl, $pelabuhan, 'progress', $pengawasId);
        $done = $this->getPengawasWorkorder($tgl, $pelabuhan, 'done', $pengawasId);

        return view('activity.result', [
            'open' => $open,
            'progress' => $progress,
            'done' => $done,
            'tgl' => date('d-m-Y', strtotime($tgl)),
        ]);
    }

    public function form_detail($id)
    {
        if (!$this->hasPerm($this->permissionCreate)) {
            return response('<div class="alert alert-danger">Anda tidak memiliki akses</div>', 403);
        }

        $workorder = $this->getWorkorderById($id);
        if (!$workorder) {
            return response('<div class="alert alert-danger">Work Order tidak ditemukan</div>', 404);
        }

        $details = $this->getWorkOrderDetails($id);

        return view('activity.form_detail', [
            'workorder' => $workorder,
            'details' => $details,
        ]);
    }

    public function list_detail($id)
    {
        if (!$this->hasPerm($this->permissionRead)) {
            return response('<div class="alert alert-danger">Anda tidak memiliki akses</div>', 403);
        }

        $workorder = $this->getWorkorderById($id);
        if (!$workorder) {
            return response('<div class="alert alert-danger">Work Order tidak ditemukan</div>', 404);
        }

        $details = $this->getWorkOrderDetails($id);

        return view('activity.list_detail', [
            'workorder' => $workorder,
            'details' => $details,
            'permission_create' => $this->hasPerm($this->permissionCreate) ? 1 : 0,
            'permission_delete' => $this->hasPerm($this->permissionDelete) ? 1 : 0,
        ]);
    }

    public function update_status(Request $request): JsonResponse
    {
        if (!$this->hasPerm($this->permissionUpdate)) {
            return $this->jsonResponse(false, 'Anda tidak memiliki hak akses');
        }

        $id = $request->input('id');
        $status = $request->input('status');

        if (empty($id) || !is_numeric($id)) {
            return $this->jsonResponse(false, 'ID tidak valid');
        }

        if (!in_array($status, ['open', 'progress', 'review', 'done'])) {
            return $this->jsonResponse(false, 'Status tidak valid');
        }

        $workorder = DB::table('t_work_order')->where('ID_WORK_ORDER', $id)->first();
        if (!$workorder) {
            return $this->jsonResponse(false, 'Data Work Order tidak ditemukan');
        }

        DB::table('t_work_order')->where('ID_WORK_ORDER', $id)->update([
            'STATUS' => $status,
            'UPDATED_BY' => auth()->id(),
            'UPDATED_AT' => now(),
        ]);

        ActivityLogService::log("Update status WO {$workorder->NO_WORK_ORDER} -> {$status}", 'update', 'work_order', [
            'affected_record_id' => $id,
            'affected_table' => 't_work_order',
            'old_values' => ['STATUS' => $workorder->STATUS],
            'new_values' => ['STATUS' => $status],
        ]);

        return $this->jsonResponse(true, "Work Order {$workorder->NO_WORK_ORDER} berhasil ditandai " . strtoupper($status));
    }

    public function store_detail(Request $request): JsonResponse
    {
        if (!$this->hasPerm($this->permissionCreate)) {
            return $this->jsonResponse(false, 'Anda tidak memiliki hak akses');
        }

        $woId = $request->input('work_order_id');
        if (empty($woId) || !is_numeric($woId)) {
            return $this->jsonResponse(false, 'Work Order ID tidak valid');
        }

        $krkIds = $request->input('krk_id');
        $hasilArr = $request->input('hasil');
        $truckArr = $request->input('jumlah_truck', []);
        $ransumArr = $request->input('ransum');
        $catatanArr = $request->input('catatan');
        $tglLaporanArr = $request->input('tgl_laporan');

        if (empty($krkIds) || !is_array($krkIds)) {
            return $this->jsonResponse(false, 'Minimal satu baris data harus diisi');
        }

        $workorder = DB::table('t_work_order')->where('ID_WORK_ORDER', $woId)->first();
        if (!$workorder) {
            return $this->jsonResponse(false, 'Work Order tidak ditemukan');
        }

        try {
            DB::beginTransaction();

            $successCount = 0;

            for ($i = 0; $i < count($krkIds); $i++) {
                $krkId = $krkIds[$i];
                if (empty($krkId) || !is_numeric($krkId)) {
                    continue;
                }

                $fotoBefore = null;
                $fotoAfter = null;

                $fileKeyBefore = 'foto_before_' . $i;
                $fileKeyAfter = 'foto_after_' . $i;

                if ($request->hasFile($fileKeyBefore)) {
                    $fotoBefore = $this->uploadFoto($request->file($fileKeyBefore));
                }
                if ($request->hasFile($fileKeyAfter)) {
                    $fotoAfter = $this->uploadFoto($request->file($fileKeyAfter));
                }

                $krk = DB::table('m_krk')->where('ID_KRK', $krkId)->first();
                $tglDetail = !empty($tglLaporanArr[$i]) ? date('Y-m-d H:i:s', strtotime($tglLaporanArr[$i])) : now();

                DB::table('t_work_order_detail')->insert([
                    'ID_WORK_ORDER' => $woId,
                    'ID_KRK' => $krkId,
                    'NAMA_KRK' => $krk ? $krk->NAMA : null,
                    'HASIL' => isset($hasilArr[$i]) ? (float)$hasilArr[$i] : 0,
                    'JUMLAH_TRUCK' => (isset($truckArr[$i]) && is_numeric($truckArr[$i])) ? (int)$truckArr[$i] : 0,
                    'RANSUM' => (isset($ransumArr[$i]) && $ransumArr[$i] !== '' && is_numeric($ransumArr[$i])) ? (int)$ransumArr[$i] : null,
                    'ID_SATUAN' => $workorder->ID_SATUAN,
                    'CATATAN' => $catatanArr[$i] ?? null,
                    'FOTO_KEGIATAN_BEFORE' => $fotoBefore,
                    'FOTO_KEGIATAN_AFTER' => $fotoAfter,
                    'CREATED_BY' => auth()->id(),
                    'UPDATED_BY' => auth()->id(),
                    'CREATED_AT' => $tglDetail,
                    'UPDATED_AT' => now(),
                ]);

                $successCount++;
            }

            // Auto-set status ke 'progress' jika WO masih 'open'
            // dan assign pengawas jika belum terassign
            $updateData = [
                'UPDATED_BY' => auth()->id(),
                'UPDATED_AT' => now(),
            ];
            if (strtolower($workorder->STATUS ?? '') === 'open') {
                $updateData['STATUS'] = 'progress';
            }
            if (empty($workorder->ID_PENGAWAS)) {
                $updateData['ID_PENGAWAS'] = auth()->id();
            }
            DB::table('t_work_order')->where('ID_WORK_ORDER', $woId)->update($updateData);

            DB::commit();

            ActivityLogService::log("Menambah {$successCount} laporan aktifitas untuk WO ID: {$woId}", 'create', 'activity');

            return $this->jsonResponse(true, "{$successCount} laporan berhasil disimpan");
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->jsonResponse(false, 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    public function delete_detail(Request $request): JsonResponse
    {
        if (!$this->hasPerm($this->permissionDelete)) {
            return $this->jsonResponse(false, 'Anda tidak memiliki hak akses');
        }

        $id = $request->input('id');
        if (empty($id) || !is_numeric($id)) {
            return $this->jsonResponse(false, 'ID tidak valid');
        }

        $detail = DB::table('t_work_order_detail')->where('ID_WORK_ORDER_DETAIL', $id)->first();
        if (!$detail) {
            return $this->jsonResponse(false, 'Data detail tidak ditemukan');
        }

        // Only creator or admin can delete
        $user = auth()->user();
        if ($detail->CREATED_BY != $user->id && !in_array((int)$user->group_id, [1, 2])) {
            return $this->jsonResponse(false, 'Anda tidak memiliki hak akses');
        }

        DB::table('t_work_order_detail')->where('ID_WORK_ORDER_DETAIL', $id)->update([
            'DELETED_AT' => now(),
            'UPDATED_BY' => $user->id,
        ]);

        ActivityLogService::log("Menghapus laporan aktifitas ID: {$id}", 'delete', 'activity');

        return $this->jsonResponse(true, 'Laporan berhasil dihapus');
    }

    private function uploadFoto($file): string
    {
        $year = date('Y');
        $month = date('m');
        $targetDir = public_path("filemanager/activity/{$year}/{$month}");
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $filename = md5(time() . '_' . $file->getClientOriginalName()) . '.' . $file->getClientOriginalExtension();
        $file->move($targetDir, $filename);

        return "filemanager/activity/{$year}/{$month}/{$filename}";
    }

    private function getWorkorderById($id)
    {
        return DB::table('t_work_order as a')
            ->leftJoin('s_users as b', 'a.ID_PENGAWAS', '=', 'b.id')
            ->leftJoin('m_pelabuhan as c', 'a.ID_PELABUHAN', '=', 'c.ID_PELABUHAN')
            ->leftJoin('m_client as d', 'a.ID_CLIENT', '=', 'd.ID_CLIENT')
            ->leftJoin('m_kapal as e', 'a.ID_KAPAL', '=', 'e.ID_KAPAL')
            ->leftJoin('m_dermaga as f', 'a.ID_DERMAGA', '=', 'f.ID_DERMAGA')
            ->leftJoin('m_aktifitas as g', 'a.ID_AKTIFITAS', '=', 'g.ID_AKTIFITAS')
            ->leftJoin('m_satuan as h', 'a.ID_SATUAN', '=', 'h.ID_SATUAN')
            ->where('a.ID_WORK_ORDER', $id)
            ->whereNull('a.DELETED_AT')
            ->select(
                'a.*',
                'b.username as PENGAWAS_USER',
                'c.NAMA as NAMA_PELABUHAN',
                'd.NAMA as NAMA_CLIENT',
                'e.NAMA as NAMA_KAPAL',
                'f.NAMA as NAMA_DERMAGA',
                'g.NAMA as NAMA_AKTIFITAS',
                'h.NAMA as NAMA_SATUAN'
            )
            ->first();
    }

    private function getWorkOrderDetails($woId): array
    {
        return DB::table('t_work_order_detail as a')
            ->leftJoin('m_krk as b', 'a.ID_KRK', '=', 'b.ID_KRK')
            ->leftJoin('s_users as c', 'a.CREATED_BY', '=', 'c.id')
            ->where('a.ID_WORK_ORDER', $woId)
            ->whereNull('a.DELETED_AT')
            ->select('a.*', 'b.NAMA as NAMA_KRK', 'c.username as CREATED_USER')
            ->orderBy('a.CREATED_AT', 'desc')
            ->get()
            ->toArray();
    }

    private function getPengawasWorkorder(?string $tgl, ?string $pelabuhan, string $status, ?int $pengawasId): array
    {
        $query = DB::table('t_work_order as a')
            ->leftJoin('s_users as b', 'a.ID_PENGAWAS', '=', 'b.id')
            ->leftJoin('m_pelabuhan as c', 'a.ID_PELABUHAN', '=', 'c.ID_PELABUHAN')
            ->leftJoin('m_client as d', 'a.ID_CLIENT', '=', 'd.ID_CLIENT')
            ->leftJoin('m_kapal as e', 'a.ID_KAPAL', '=', 'e.ID_KAPAL')
            ->leftJoin('m_dermaga as f', 'a.ID_DERMAGA', '=', 'f.ID_DERMAGA')
            ->leftJoin('m_aktifitas as g', 'a.ID_AKTIFITAS', '=', 'g.ID_AKTIFITAS')
            ->leftJoin('m_satuan as h', 'a.ID_SATUAN', '=', 'h.ID_SATUAN')
            ->leftJoin('t_work_order_detail as l', function ($join) use ($tgl, $status) {
                $join->on('l.ID_WORK_ORDER', '=', 'a.ID_WORK_ORDER')
                     ->whereNull('l.DELETED_AT');
                if (!empty($tgl) && $status !== 'done') {
                    $join->whereDate('l.CREATED_AT', $tgl);
                }
            })
            ->whereNull('a.DELETED_AT')
            ->whereRaw("LOWER(a.STATUS) != 'hide'")
            ->where('a.STATUS', $status);

        if (!empty($pelabuhan)) {
            $query->where('a.ID_PELABUHAN', $pelabuhan);
        }

        if (!empty($pengawasId)) {
            $query->where(function ($q) use ($pengawasId) {
                $q->whereNull('a.ID_PENGAWAS')
                  ->orWhere('a.ID_PENGAWAS', $pengawasId);
            });
        }

        return $query->groupBy('a.ID_WORK_ORDER')
            ->select([
                'a.*',
                'b.username as PENGAWAS_USER',
                'c.NAMA as NAMA_PELABUHAN',
                'd.NAMA as NAMA_CLIENT',
                'e.NAMA as NAMA_KAPAL',
                'f.NAMA as NAMA_DERMAGA',
                'g.NAMA as NAMA_AKTIFITAS',
                'h.NAMA as NAMA_SATUAN',
                DB::raw("(SELECT GROUP_CONCAT(wa.NAMA_ALAT SEPARATOR ', ') FROM t_work_order_alat wa WHERE wa.ID_WORK_ORDER = a.ID_WORK_ORDER AND wa.DELETED_AT IS NULL) as NAMA_ALAT"),
                DB::raw("COUNT(l.ID_WORK_ORDER_DETAIL) as total"),
            ])
            ->get()
            ->toArray();
    }
}
