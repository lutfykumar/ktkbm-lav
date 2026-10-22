<?php

namespace App\Http\Controllers;

use App\Models\Workorder;
use App\Models\WorkorderAlat;
use App\Models\WorkorderDetail;
use App\Models\Dermaga;
use App\Models\Aktifitas;
use App\Services\WorkorderService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class WorkorderController extends Controller
{
    protected string $slug = 'tasks-work-order';
    protected string $permissionRead;
    protected string $permissionCreate;
    protected string $permissionUpdate;
    protected string $permissionDelete;
    protected WorkorderService $workorderService;

    public function __construct(WorkorderService $workorderService)
    {
        $this->workorderService = $workorderService;
        $this->permissionRead   = 'read-' . $this->slug;
        $this->permissionCreate = 'create-' . $this->slug;
        $this->permissionUpdate = 'update-' . $this->slug;
        $this->permissionDelete = 'delete-' . $this->slug;
    }

    private function hasPerm(string $permission): bool
    {
        $user = auth()->user();
        if (!$user) return false;
        if ($user->group_id === 1) return true;
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

        $canCreate = $this->hasPerm($this->permissionCreate);

        return view('workorder.index', [
            'title' => 'Work Order',
            'title_header' => 'Tasks',
            'title_parent' => 'Work Order',
            'permission_create' => $canCreate ? 1 : 0,
        ]);
    }

    public function getNoWorkOrder(Request $request): JsonResponse
    {
        $tgl = $request->input('tgl');
        if (!empty($tgl)) {
            $cleanTgl = str_replace('/', '-', $tgl);
            $timestamp = strtotime($cleanTgl);
            $tgl = $timestamp ? date('Y-m-d', $timestamp) : date('Y-m-d');
        } else {
            $tgl = date('Y-m-d');
        }

        $no = $this->workorderService->generateNoWorkOrder($tgl);

        return $this->jsonResponse(true, 'OK', [
            'no_work_order' => $no,
            'data' => [
                'no_work_order' => $no
            ]
        ]);
    }

    public function table(Request $request)
    {
        if (!$this->hasPerm($this->permissionRead)) {
            return response()->json([]);
        }

        $tStart = $request->input('tgl_start', '');
        $tEnd = $request->input('tgl_end', '');
        $pelabuhan = $request->input('pelabuhan', '');
        $client = $request->input('client', '');
        $status = $request->input('status', '');
        $showDeleted = (int)$request->input('show_deleted', 0);

        $results = $this->workorderService->tableList($tStart, $tEnd, $pelabuhan, $client, $status, $showDeleted);
        $data = [];

        foreach ($results as $v) {
            $action = $this->generateActionButtons($v, (bool)$showDeleted);

            $statusBadge = match (strtolower($v->STATUS ?? '')) {
                'open' => '<span class="badge bg-info">Open</span>',
                'progress' => '<span class="badge bg-primary">Progress</span>',
                'review' => '<span class="badge bg-warning text-dark"><i class="fal fa-clock me-1"></i>Menunggu Verifikasi</span>',
                'done' => '<span class="badge bg-success">Done</span>',
                default => '<span class="badge bg-secondary">' . ucfirst(e($v->STATUS ?? '')) . '</span>',
            };

            $data[] = [
                !empty($v->NO_WORK_ORDER) ? $v->NO_WORK_ORDER : '-',
                !empty($v->NAMA_CLIENT) ? $v->NAMA_CLIENT : '-',
                !empty($v->NAMA_KAPAL) ? $v->NAMA_KAPAL : '-',
                !empty($v->NAMA_PELABUHAN) ? $v->NAMA_PELABUHAN : '-',
                !empty($v->NAMA_DERMAGA) ? $v->NAMA_DERMAGA : '-',
                !empty($v->NAMA_AKTIFITAS) ? $v->NAMA_AKTIFITAS : '-',
                $statusBadge,
                !empty($v->CREATED_USER) ? $v->CREATED_USER : '-',
                !empty($v->CREATED_AT) ? $v->CREATED_AT : '-',
                !empty($action) ? $action : '-',
            ];
        }

        return response()->json($data);
    }

    private function generateActionButtons($data, bool $showDeleted = false): string
    {
        $btnName = !empty($data->NO_WORK_ORDER) ? $data->NO_WORK_ORDER : '-';
        $btnId = $data->ID_WORK_ORDER;
        $action = '';

        if ($showDeleted) {
            if ($this->hasPerm($this->permissionUpdate)) {
                $action .= '<button type="button" class="btn btn-sm btn-success btn-icon waves-effect waves-light me-1 btn-restore"
                                data-bs-toggle="tooltip" data-bs-placement="top"
                                title="Restore : ' . e($btnName) . '"
                                data-title="' . e($btnName) . '"
                                data-id="' . $btnId . '">
                            <i class="fal fa-trash-restore"></i>
                        </button>';
            }
            return !empty($action) ? $action : '-';
        }

        $hasLaporan = !empty($data->total_laporan) && $data->total_laporan > 0;
        $isOpen = strtolower($data->STATUS ?? '') === 'open';
        if ($this->hasPerm($this->permissionRead) && (!$isOpen || $hasLaporan)) {
            $action .= '<button type="button" class="btn btn-sm btn-primary btn-icon waves-effect waves-light me-1 btn-detail"
                            data-bs-toggle="tooltip" data-bs-placement="top"
                            title="Detail : ' . e($btnName) . '"
                            data-id="' . $btnId . '">
                        <i class="fal fa-eye"></i>
                    </button>';
        }

        if ($this->hasPerm($this->permissionUpdate) && strtolower($data->STATUS ?? '') === 'review') {
            $action .= '<button type="button" class="btn btn-sm btn-info btn-icon waves-effect waves-light me-1 btn-quick-done"
                            data-bs-toggle="tooltip" data-bs-placement="top"
                            title="Sahkan & Kunci (DONE): ' . e($btnName) . '"
                            data-title="' . e($btnName) . '"
                            data-id="' . $btnId . '">
                        <i class="fal fa-check-double"></i>
                    </button>';
        }

        if ($this->hasPerm($this->permissionUpdate)) {
            $action .= '<button type="button" class="btn btn-sm btn-success btn-icon waves-effect waves-light me-1 btn-edit"
                            data-bs-toggle="tooltip" data-bs-placement="top"
                            title="Update : ' . e($btnName) . '"
                            data-id="' . $btnId . '">
                        <i class="fal fa-edit"></i>
                    </button>';
        }

        if ($this->hasPerm($this->permissionDelete)) {
            $action .= '<button type="button" class="btn btn-sm btn-danger btn-icon waves-effect waves-light btn-delete"
                            data-bs-toggle="tooltip" data-bs-placement="top"
                            title="Delete : ' . e($btnName) . '"
                            data-title="' . e($btnName) . '"
                            data-id="' . $btnId . '">
                        <i class="fal fa-trash"></i>
                    </button>';
        }

        return !empty($action) ? $action : '-';
    }

    public function store(Request $request): JsonResponse
    {
        if (!$this->hasPerm($this->permissionCreate)) {
            return $this->jsonResponse(false, 'Anda tidak memiliki hak akses');
        }

        $clientId = $request->input('client_id');
        $kapalId = $request->input('kapal_id');
        $dermagaId = $request->input('dermaga_id');
        $alatIds = $request->input('alat_id'); // array
        $aktifitasId = $request->input('aktifitas_id');
        $pengawasId = $request->input('pengawas_id');
        $status = $request->input('status', 'open');
        $tglWorkOrder = $request->input('tgl_work_order');
        $inputNoWorkOrder = trim($request->input('no_work_order', ''));
        $isManualNo = $request->input('is_manual_no');

        if (empty($clientId) || !is_numeric($clientId)) {
            return $this->jsonResponse(false, 'Client wajib dipilih');
        }
        if (empty($kapalId) || !is_numeric($kapalId)) {
            return $this->jsonResponse(false, 'Kapal wajib dipilih');
        }
        if (empty($dermagaId) || !is_numeric($dermagaId)) {
            return $this->jsonResponse(false, 'Dermaga wajib dipilih');
        }

        if (!empty($tglWorkOrder)) {
            $cleanTgl = str_replace('/', '-', $tglWorkOrder);
            $timestamp = strtotime($cleanTgl);
            $tglWorkOrder = $timestamp ? date('Y-m-d', $timestamp) : date('Y-m-d');
        } else {
            $tglWorkOrder = date('Y-m-d');
        }

        if (!empty($isManualNo) && !empty($inputNoWorkOrder)) {
            $check = Workorder::where('NO_WORK_ORDER', $inputNoWorkOrder)->first();
            if ($check) {
                return $this->jsonResponse(false, 'Nomor Work Order sudah digunakan');
            }
            $noWorkOrder = $inputNoWorkOrder;
        } else {
            $noWorkOrder = $this->workorderService->generateNoWorkOrder($tglWorkOrder);
        }

        try {
            DB::beginTransaction();

            $dermaga = Dermaga::find($dermagaId);
            $aktifitas = !empty($aktifitasId) ? Aktifitas::find($aktifitasId) : null;
            $userId = auth()->id();
            $createdAt = date('Y-m-d H:i:s', strtotime($tglWorkOrder . ' ' . date('H:i:s')));

            $wo = Workorder::create([
                'NO_WORK_ORDER' => $noWorkOrder,
                'ID_CLIENT' => $clientId,
                'ID_PELABUHAN' => $dermaga ? $dermaga->ID_PELABUHAN : null,
                'ID_KAPAL' => $kapalId,
                'ID_DERMAGA' => $dermagaId,
                'ID_AKTIFITAS' => !empty($aktifitasId) ? $aktifitasId : null,
                'ID_SATUAN' => $aktifitas ? $aktifitas->ID_SATUAN : null,
                'ID_PENGAWAS' => !empty($pengawasId) ? $pengawasId : null,
                'STATUS' => !empty($status) ? $status : 'open',
                'CREATED_BY' => $userId,
                'UPDATED_BY' => $userId,
                'CREATED_AT' => $createdAt,
                'UPDATED_AT' => now(),
            ]);

            if (!empty($alatIds) && is_array($alatIds)) {
                $this->workorderService->saveWoAlat($wo->ID_WORK_ORDER, $alatIds, $userId);
            }

            DB::commit();

            return $this->jsonResponse(true, 'Data berhasil ditambahkan');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->jsonResponse(false, 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    public function detail(Request $request)
    {
        if (!$this->hasPerm($this->permissionRead)) {
            return '<div class="alert alert-danger">Akses tidak diizinkan</div>';
        }

        $id = $request->input('id');
        if (empty($id) || !is_numeric($id)) {
            return '<div class="alert alert-danger">ID tidak valid</div>';
        }

        $model = $this->workorderService->getWorkorder((int)$id);
        if (!$model) {
            return '<div class="alert alert-danger">Data tidak ditemukan</div>';
        }

        $details = $this->workorderService->getWorkOrderDetails((int)$id);
        $woAlat = $this->workorderService->getWoAlat((int)$id);

        return view('workorder.detail', [
            'title' => 'Work Order',
            'model' => $model,
            'details' => $details,
            'wo_alat' => $woAlat,
        ]);
    }

    public function edit(Request $request)
    {
        if (!$this->hasPerm($this->permissionUpdate)) {
            return '<div class="alert alert-danger">Akses tidak diizinkan</div>';
        }

        $id = $request->input('id');
        if (empty($id) || !is_numeric($id)) {
            return '<div class="alert alert-danger">ID tidak valid</div>';
        }

        $model = $this->workorderService->getWorkorder((int)$id);
        if (!$model) {
            return '<div class="alert alert-danger">Data tidak ditemukan</div>';
        }

        $woAlat = $this->workorderService->getWoAlat((int)$id);

        return view('workorder.edit', [
            'title' => 'Work Order',
            'model' => $model,
            'wo_alat' => $woAlat,
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        if (!$this->hasPerm($this->permissionUpdate)) {
            return $this->jsonResponse(false, 'Anda tidak memiliki hak akses');
        }

        $id = $request->input('model_id');
        if (empty($id) || !is_numeric($id)) {
            return $this->jsonResponse(false, 'ID tidak valid');
        }

        $uClientId = $request->input('u_client_id');
        $uKapalId = $request->input('u_kapal_id');
        $uDermagaId = $request->input('u_dermaga_id');
        $uAlatIds = $request->input('u_alat_id'); // array
        $uAktifitasId = $request->input('u_aktifitas_id');
        $uPengawasId = $request->input('u_pengawas_id');
        $uStatus = $request->input('u_status');
        $uNoWorkOrder = trim($request->input('u_no_work_order', ''));
        $uTglWorkOrder = $request->input('u_tgl_work_order');

        if (empty($uClientId) || !is_numeric($uClientId)) {
            return $this->jsonResponse(false, 'Client wajib dipilih');
        }
        if (empty($uKapalId) || !is_numeric($uKapalId)) {
            return $this->jsonResponse(false, 'Kapal wajib dipilih');
        }
        if (empty($uDermagaId) || !is_numeric($uDermagaId)) {
            return $this->jsonResponse(false, 'Dermaga wajib dipilih');
        }

        $existing = Workorder::find($id);
        if (!$existing) {
            return $this->jsonResponse(false, 'Data tidak ditemukan');
        }

        if (!empty($uNoWorkOrder) && $uNoWorkOrder !== $existing->NO_WORK_ORDER) {
            $duplicate = Workorder::where('NO_WORK_ORDER', $uNoWorkOrder)
                ->where('ID_WORK_ORDER', '!=', $id)
                ->first();
            if ($duplicate) {
                return $this->jsonResponse(false, 'Nomor Work Order sudah digunakan oleh data lain');
            }
        }

        try {
            DB::beginTransaction();

            $dermaga = Dermaga::find($uDermagaId);
            $aktifitas = !empty($uAktifitasId) ? Aktifitas::find($uAktifitasId) : null;
            $userId = auth()->id();

            $updateData = [
                'ID_CLIENT' => $uClientId,
                'ID_PELABUHAN' => $dermaga ? $dermaga->ID_PELABUHAN : $existing->ID_PELABUHAN,
                'ID_KAPAL' => $uKapalId,
                'ID_DERMAGA' => $uDermagaId,
                'ID_AKTIFITAS' => !empty($uAktifitasId) ? $uAktifitasId : $existing->ID_AKTIFITAS,
                'ID_SATUAN' => $aktifitas ? $aktifitas->ID_SATUAN : $existing->ID_SATUAN,
                'ID_PENGAWAS' => !empty($uPengawasId) ? $uPengawasId : $existing->ID_PENGAWAS,
                'STATUS' => !empty($uStatus) ? $uStatus : $existing->STATUS,
                'UPDATED_BY' => $userId,
                'UPDATED_AT' => now(),
            ];

            if (!empty($uNoWorkOrder)) {
                $updateData['NO_WORK_ORDER'] = $uNoWorkOrder;
            }

            if (!empty($uTglWorkOrder)) {
                $cleanUTgl = str_replace('/', '-', $uTglWorkOrder);
                $uTimestamp = strtotime($cleanUTgl);
                $cleanUDate = $uTimestamp ? date('Y-m-d', $uTimestamp) : date('Y-m-d');
                $timePart = !empty($existing->CREATED_AT) ? date('H:i:s', strtotime($existing->CREATED_AT)) : date('H:i:s');
                $updateData['CREATED_AT'] = date('Y-m-d H:i:s', strtotime($cleanUDate . ' ' . $timePart));
            }

            $existing->update($updateData);

            if (is_array($uAlatIds)) {
                $this->workorderService->saveWoAlat($id, $uAlatIds, $userId);
            }

            DB::commit();

            return $this->jsonResponse(true, 'Data berhasil diupdate');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->jsonResponse(false, 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    public function updateStatus(Request $request): JsonResponse
    {
        if (!$this->hasPerm($this->permissionUpdate)) {
            return $this->jsonResponse(false, 'Anda tidak memiliki hak akses');
        }

        $id = $request->input('id');
        $status = strtolower($request->input('status', ''));

        if (empty($id) || !is_numeric($id)) {
            return $this->jsonResponse(false, 'ID tidak valid');
        }

        $allowed = ['hide', 'open', 'progress', 'review', 'done'];
        if (!in_array($status, $allowed)) {
            return $this->jsonResponse(false, 'Status tidak valid');
        }

        $workorder = Workorder::find($id);
        if (!$workorder) {
            return $this->jsonResponse(false, 'Data tidak ditemukan');
        }

        $workorder->update([
            'STATUS' => $status,
            'UPDATED_BY' => auth()->id(),
            'UPDATED_AT' => now(),
        ]);

        return $this->jsonResponse(true, 'Status Work Order ' . $workorder->NO_WORK_ORDER . ' berhasil diubah menjadi ' . strtoupper($status));
    }

    public function delete(Request $request): JsonResponse
    {
        if (!$this->hasPerm($this->permissionDelete)) {
            return $this->jsonResponse(false, 'Anda tidak memiliki hak akses');
        }

        $id = $request->input('id');
        if (empty($id) || !is_numeric($id)) {
            return $this->jsonResponse(false, 'ID tidak valid');
        }

        $workorder = Workorder::find($id);
        if (!$workorder) {
            return $this->jsonResponse(false, 'Data tidak ditemukan');
        }

        $workorder->delete();

        return $this->jsonResponse(true, 'Data berhasil dihapus');
    }

    public function restore(Request $request): JsonResponse
    {
        if (!$this->hasPerm($this->permissionUpdate)) {
            return $this->jsonResponse(false, 'Anda tidak memiliki hak akses');
        }

        $id = $request->input('id');
        $workorder = Workorder::onlyTrashed()->find($id);
        if (!$workorder) {
            return $this->jsonResponse(false, 'Data tidak ditemukan');
        }

        $workorder->restore();

        return $this->jsonResponse(true, 'Data berhasil dipulihkan');
    }
}
