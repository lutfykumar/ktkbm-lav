<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Kapal;
use App\Models\UpahTkbm;
use App\Models\Workorder;
use App\Services\UpahTkbmService;
use App\Services\WorkorderService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class UpahTkbmController extends Controller
{
    protected string $slug = 'accounting-upah';
    protected string $permissionRead;
    protected string $permissionCreate;
    protected string $permissionUpdate;
    protected string $permissionDelete;
    protected UpahTkbmService $upahService;
    protected WorkorderService $workorderService;

    public function __construct(UpahTkbmService $upahService, WorkorderService $workorderService)
    {
        $this->upahService = $upahService;
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

        return view('accounting.upah.index', [
            'title' => 'Upah & Kwitansi TKBM',
            'title_header' => 'Upah & Kwitansi TKBM',
            'title_parent' => 'Accounting',
            'permission_create' => $this->hasPerm($this->permissionCreate) ? 1 : 0,
            'permission_update' => $this->hasPerm($this->permissionUpdate) ? 1 : 0,
            'permission_delete' => $this->hasPerm($this->permissionDelete) ? 1 : 0,
            'stats' => $this->upahService->dashboardStats(),
        ]);
    }

    public function table(Request $request)
    {
        if (!$this->hasPerm($this->permissionRead)) {
            return response()->json([]);
        }

        $tStart      = $request->input('tgl_start', '');
        $tEnd        = $request->input('tgl_end', '');
        $client      = $request->input('client', '');
        $status      = $request->input('status', '');
        $showDeleted = (int)$request->input('show_deleted', 0);

        $results = $this->upahService->tableList($tStart, $tEnd, $client, $status, $showDeleted);
        $data = [];

        foreach ($results as $v) {
            $action = $this->generateActionButtons($v, (bool)$showDeleted);

            $statusBadge = match (strtolower($v->STATUS ?? '')) {
                'draft' => '<span class="badge bg-secondary">Draft</span>',
                'published' => '<span class="badge bg-info">Terbit</span>',
                'paid' => '<span class="badge bg-success">Lunas</span>',
                'cancelled' => '<span class="badge bg-dark">Dibatalkan</span>',
                default => '<span class="badge bg-secondary">' . ucfirst(e($v->STATUS ?? '')) . '</span>',
            };

            $periode = '-';
            if (!empty($v->TGL_START_FORMAT) && !empty($v->TGL_END_FORMAT)) {
                $periode = $v->TGL_START_FORMAT . ' - ' . $v->TGL_END_FORMAT;
            } elseif (!empty($v->TGL_START_FORMAT)) {
                $periode = $v->TGL_START_FORMAT;
            }

            $data[] = [
                !empty($v->NO_KWITANSI) ? '<strong>' . e($v->NO_KWITANSI) . '</strong><br><small class="text-muted">' . e($v->NO_DOKUMEN) . '</small>' : e($v->NO_DOKUMEN),
                !empty($v->NAMA_CLIENT) ? e($v->NAMA_CLIENT) : '-',
                !empty($v->NAMA_KAPAL) ? '<strong>' . e($v->NAMA_KAPAL) . '</strong><br><small class="text-muted">' . e($v->KOMODITAS ?: '-') . '</small>' : '-',
                number_format(floatval($v->TOTAL_VOLUME), 3, ',', '.') . ' Ton<br><small class="text-muted">' . intval($v->TOTAL_TRUCK) . ' Truck</small>',
                '<strong>Rp ' . ribuan($v->GRAND_TOTAL) . '</strong>',
                $statusBadge,
                $periode,
                !empty($action) ? $action : '-',
            ];
        }

        return response()->json($data);
    }

    private function generateActionButtons($data, bool $showDeleted = false): string
    {
        $btnId   = $data->ID_UPAH_TKBM;
        $btnName = $data->NO_KWITANSI ?: $data->NO_DOKUMEN;
        $action   = '<div class="btn-group">';

        if ($showDeleted) {
            if ($this->hasPerm($this->permissionUpdate)) {
                $action .= '<button type="button" class="btn btn-sm btn-success btn-icon waves-effect btn-restore"
                                data-bs-toggle="tooltip" title="Restore" data-id="' . $btnId . '">
                            <i class="fal fa-trash-restore"></i></button>';
            }
            $action .= '</div>';
            return $action;
        }

        if ($this->hasPerm($this->permissionRead)) {
            $action .= '<a href="' . url('accounting/upah/detail/' . $btnId) . '" class="btn btn-sm btn-info btn-icon waves-effect"
                          data-bs-toggle="tooltip" title="Detail"><i class="fal fa-eye"></i></a>';

            // Dropdown print shortcuts
            $action .= '<div class="btn-group">
                <button type="button" class="btn btn-sm btn-secondary btn-icon waves-effect dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" title="Cetak Berkas">
                    <i class="fal fa-print"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="' . url('accounting/upah/print_perhitungan/' . $btnId) . '" target="_blank"><i class="fal fa-file-invoice me-1 text-primary"></i> Lembar Perhitungan Upah</a></li>
                    <li><a class="dropdown-item" href="' . url('accounting/upah/print_tally/' . $btnId) . '" target="_blank"><i class="fal fa-truck me-1 text-info"></i> Rekap Tally Truk & Tonase</a></li>
                    <li><a class="dropdown-item" href="' . url('accounting/upah/print_kwitansi/' . $btnId) . '" target="_blank"><i class="fal fa-receipt me-1 text-success"></i> Kwitansi Resmi K.TKBM</a></li>
                </ul>
            </div>';
        }

        if ($this->hasPerm($this->permissionUpdate) && in_array($data->STATUS, ['draft', 'published'])) {
            $action .= '<a href="' . url('accounting/upah/edit/' . $btnId) . '" class="btn btn-sm btn-warning btn-icon waves-effect"
                          data-bs-toggle="tooltip" title="Edit"><i class="fal fa-pen"></i></a>';
        }

        if ($this->hasPerm($this->permissionDelete) && in_array($data->STATUS, ['draft', 'published'])) {
            $action .= '<button type="button" class="btn btn-sm btn-danger btn-icon waves-effect btn-delete"
                            data-bs-toggle="tooltip" title="Hapus" data-id="' . $btnId . '" data-title="' . e($btnName) . '">
                        <i class="fal fa-trash-alt"></i></button>';
        }

        $action .= '</div>';
        return $action;
    }

    public function create($wo_id = null)
    {
        if (!$this->hasPerm($this->permissionCreate)) {
            abort(403, 'Akses tidak diizinkan');
        }

        $pulled = [];
        $wo = null;
        if (!empty($wo_id)) {
            $pulled = $this->upahService->pullFromWorkorder((int)$wo_id);
            $wo = $pulled['workorder'] ?? null;
        }

        $completedWorkorders = Workorder::with(['client', 'kapal', 'dermaga'])
            ->whereIn('STATUS', ['done', 'review'])
            ->orderByDesc('ID_WORK_ORDER')
            ->limit(100)
            ->get();

        return view('accounting.upah.form', [
            'mode' => 'create',
            'wo' => $wo,
            'pulled' => $pulled,
            'completedWorkorders' => $completedWorkorders,
            'autoNoDokumen' => $this->upahService->generateNoDokumen(),
            'autoNoKwitansi' => $this->upahService->generateNoKwitansi(),
            'title' => 'Buat Perhitungan & Kwitansi Upah',
            'title_header' => 'Buat Perhitungan Upah Baru',
            'title_parent' => 'Upah & Kwitansi TKBM',
        ]);
    }

    public function pullWoData(Request $request): JsonResponse
    {
        $woId = $request->input('wo_id');
        if (empty($woId) || !is_numeric($woId)) {
            return $this->jsonResponse(false, 'Work Order tidak valid');
        }

        $data = $this->upahService->pullFromWorkorder((int)$woId);
        if (empty($data)) {
            return $this->jsonResponse(false, 'Data Work Order tidak ditemukan');
        }

        return $this->jsonResponse(true, 'Data berhasil dimuat', $data);
    }

    public function store(Request $request): JsonResponse
    {
        if (!$this->hasPerm($this->permissionCreate)) {
            return $this->jsonResponse(false, 'Anda tidak memiliki hak akses');
        }

        $woId = $request->input('id_work_order');
        if (empty($woId) || !is_numeric($woId)) {
            return $this->jsonResponse(false, 'Work Order wajib dipilih');
        }

        $wo = Workorder::find($woId);
        if (!$wo) {
            return $this->jsonResponse(false, 'Work Order tidak ditemukan');
        }

        $tglStart = $request->input('tgl_kegiatan_start', date('Y-m-d'));
        $tglEnd = $request->input('tgl_kegiatan_end', date('Y-m-d'));
        $komoditas = $request->input('komoditas', '');

        // Detail arrays
        $tglKerjaArr = $request->input('tgl_kerja', []);
        $jamKerjaArr = $request->input('jam_kerja', []);
        $gangArr     = $request->input('jumlah_gang', []);
        $harianArr   = $request->input('jumlah_harian', []);
        $truckArr    = $request->input('jumlah_truck', []);
        $boronganArr = $request->input('volume_borongan', []);
        $upahArr     = $request->input('upah_nominal', []);
        $admArr      = $request->input('adm_nominal', []);
        $toeslagArr  = $request->input('toeslag_nominal', []);
        $lemburArr   = $request->input('lembur_nominal', []);
        $ketArr      = $request->input('keterangan', []);

        if (empty($tglKerjaArr) || !is_array($tglKerjaArr)) {
            return $this->jsonResponse(false, 'Minimal harus ada satu baris rincian kegiatan');
        }

        $details = [];
        $totalTruck = 0;
        $totalVolume = 0;
        $subtotalUpah = 0;
        $totalAdm = 0;
        $totalToeslag = 0;
        $totalLembur = 0;

        for ($i = 0; $i < count($tglKerjaArr); $i++) {
            if (empty($tglKerjaArr[$i])) continue;

            $gang = intval($gangArr[$i] ?? 1);
            $harian = intval($harianArr[$i] ?? 0);
            $truck = intval($truckArr[$i] ?? 0);
            $vol = floatval($boronganArr[$i] ?? 0);
            $upah = floatval($upahArr[$i] ?? 0);
            $adm = floatval($admArr[$i] ?? 0);
            $toeslag = floatval($toeslagArr[$i] ?? 0);
            $lembur = floatval($lemburArr[$i] ?? 0);
            $subtotalBaris = $upah + $adm + $toeslag + $lembur;

            $totalTruck += $truck;
            $totalVolume += $vol;
            $subtotalUpah += $upah;
            $totalAdm += $adm;
            $totalToeslag += $toeslag;
            $totalLembur += $lembur;

            $details[] = [
                'tgl_kerja' => $tglKerjaArr[$i],
                'jam_kerja' => $jamKerjaArr[$i] ?? '07 - 19',
                'jumlah_gang' => $gang,
                'jumlah_harian' => $harian,
                'jumlah_truck' => $truck,
                'volume_borongan' => $vol,
                'upah_nominal' => $upah,
                'adm_nominal' => $adm,
                'toeslag_nominal' => $toeslag,
                'lembur_nominal' => $lembur,
                'jumlah_upah' => $subtotalBaris,
                'keterangan' => $ketArr[$i] ?? null,
            ];
        }

        $grandTotal = $subtotalUpah + $totalAdm + $totalToeslag + $totalLembur;
        $alokasiBuruh = floatval($request->input('alokasi_buruh', $subtotalUpah + $totalLembur + ($totalToeslag * 0.8)));
        $alokasiKoperasi = floatval($request->input('alokasi_koperasi', $grandTotal - $alokasiBuruh));

        if (abs(($alokasiBuruh + $alokasiKoperasi) - $grandTotal) > 1) {
            return $this->jsonResponse(false, 'Total Alokasi Buruh (Rp ' . ribuan($alokasiBuruh) . ') + Koperasi (Rp ' . ribuan($alokasiKoperasi) . ') harus sama dengan Grand Total (Rp ' . ribuan($grandTotal) . '). Selisih: Rp ' . ribuan(abs(($alokasiBuruh + $alokasiKoperasi) - $grandTotal)));
        }

        $headerData = [
            'NO_DOKUMEN' => $request->input('no_dokumen') ?: $this->upahService->generateNoDokumen($tglStart),
            'NO_KWITANSI' => $request->input('no_kwitansi') ?: $this->upahService->generateNoKwitansi($tglStart),
            'ID_WORK_ORDER' => $wo->ID_WORK_ORDER,
            'ID_CLIENT' => $wo->ID_CLIENT,
            'ID_KAPAL' => $wo->ID_KAPAL,
            'ID_DERMAGA' => $wo->ID_DERMAGA,
            'KOMODITAS' => $komoditas,
            'TGL_KEGIATAN_START' => $tglStart,
            'TGL_KEGIATAN_END' => $tglEnd,
            'TOTAL_TRUCK' => $totalTruck,
            'TOTAL_VOLUME' => $totalVolume,
            'SUBTOTAL_UPAH' => $subtotalUpah,
            'TOTAL_ADM' => $totalAdm,
            'TOTAL_TOESLAG' => $totalToeslag,
            'TOTAL_LEMBUR' => $totalLembur,
            'GRAND_TOTAL' => $grandTotal,
            'ALOKASI_BURUH' => $alokasiBuruh,
            'ALOKASI_KOPERASI' => $alokasiKoperasi,
            'REKENING_BANK' => $request->input('rekening_bank', '(BCA) 6100091044 / (MANDIRI) 1780068844444 AN. ERNAWATI/THOHIR'),
            'KOORDINATOR_NAMA' => $request->input('koordinator_nama', 'H. M. THOHIR, SE'),
            'STATUS' => $request->input('status', 'draft'),
            'CATATAN' => $request->input('catatan'),
        ];

        try {
            $id = $this->upahService->store($headerData, $details, auth()->id());
            return $this->jsonResponse(true, 'Data perhitungan upah & kwitansi berhasil disimpan', ['id' => $id]);
        } catch (\Exception $e) {
            return $this->jsonResponse(false, 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    public function detail($id)
    {
        if (!$this->hasPerm($this->permissionRead)) {
            abort(403, 'Akses tidak diizinkan');
        }

        $upah = $this->upahService->getUpahTkbm((int)$id);
        if (!$upah) {
            abort(404, 'Data Upah TKBM tidak ditemukan');
        }

        $details = $this->upahService->getUpahTkbmDetails((int)$id);

        return view('accounting.upah.detail', [
            'upah' => $upah,
            'details' => $details,
            'title' => 'Detail Upah & Kwitansi',
            'title_header' => 'Detail ' . ($upah->NO_KWITANSI ?: $upah->NO_DOKUMEN),
            'title_parent' => 'Upah & Kwitansi TKBM',
            'permission_update' => $this->hasPerm($this->permissionUpdate) ? 1 : 0,
        ]);
    }

    public function edit($id)
    {
        if (!$this->hasPerm($this->permissionUpdate)) {
            abort(403, 'Akses tidak diizinkan');
        }

        $upah = $this->upahService->getUpahTkbm((int)$id);
        if (!$upah) {
            abort(404, 'Data Upah TKBM tidak ditemukan');
        }

        $details = $this->upahService->getUpahTkbmDetails((int)$id);
        $completedWorkorders = Workorder::with(['client', 'kapal', 'dermaga'])->limit(50)->get();

        return view('accounting.upah.form', [
            'mode' => 'edit',
            'upah' => $upah,
            'details' => $details,
            'completedWorkorders' => $completedWorkorders,
            'title' => 'Edit Perhitungan & Kwitansi Upah',
            'title_header' => 'Edit Dokumen ' . ($upah->NO_KWITANSI ?: $upah->NO_DOKUMEN),
            'title_parent' => 'Upah & Kwitansi TKBM',
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        if (!$this->hasPerm($this->permissionUpdate)) {
            return $this->jsonResponse(false, 'Anda tidak memiliki hak akses');
        }

        $id = $request->input('id_upah_tkbm');
        $upah = UpahTkbm::find($id);
        if (!$upah) {
            return $this->jsonResponse(false, 'Data tidak ditemukan');
        }

        if ($upah->STATUS === 'paid') {
            return $this->jsonResponse(false, 'Dokumen yang sudah Lunas tidak dapat diubah');
        }

        $tglStart = $request->input('tgl_kegiatan_start', $upah->TGL_KEGIATAN_START);
        $tglEnd = $request->input('tgl_kegiatan_end', $upah->TGL_KEGIATAN_END);
        $komoditas = $request->input('komoditas', $upah->KOMODITAS);

        $tglKerjaArr = $request->input('tgl_kerja', []);
        $jamKerjaArr = $request->input('jam_kerja', []);
        $gangArr     = $request->input('jumlah_gang', []);
        $harianArr   = $request->input('jumlah_harian', []);
        $truckArr    = $request->input('jumlah_truck', []);
        $boronganArr = $request->input('volume_borongan', []);
        $upahArr     = $request->input('upah_nominal', []);
        $admArr      = $request->input('adm_nominal', []);
        $toeslagArr  = $request->input('toeslag_nominal', []);
        $lemburArr   = $request->input('lembur_nominal', []);
        $ketArr      = $request->input('keterangan', []);

        if (empty($tglKerjaArr) || !is_array($tglKerjaArr)) {
            return $this->jsonResponse(false, 'Minimal harus ada satu baris rincian kegiatan');
        }

        $details = [];
        $totalTruck = 0;
        $totalVolume = 0;
        $subtotalUpah = 0;
        $totalAdm = 0;
        $totalToeslag = 0;
        $totalLembur = 0;

        for ($i = 0; $i < count($tglKerjaArr); $i++) {
            if (empty($tglKerjaArr[$i])) continue;

            $gang = intval($gangArr[$i] ?? 1);
            $harian = intval($harianArr[$i] ?? 0);
            $truck = intval($truckArr[$i] ?? 0);
            $vol = floatval($boronganArr[$i] ?? 0);
            $upahVal = floatval($upahArr[$i] ?? 0);
            $adm = floatval($admArr[$i] ?? 0);
            $toeslag = floatval($toeslagArr[$i] ?? 0);
            $lembur = floatval($lemburArr[$i] ?? 0);
            $subtotalBaris = $upahVal + $adm + $toeslag + $lembur;

            $totalTruck += $truck;
            $totalVolume += $vol;
            $subtotalUpah += $upahVal;
            $totalAdm += $adm;
            $totalToeslag += $toeslag;
            $totalLembur += $lembur;

            $details[] = [
                'tgl_kerja' => $tglKerjaArr[$i],
                'jam_kerja' => $jamKerjaArr[$i] ?? '07 - 19',
                'jumlah_gang' => $gang,
                'jumlah_harian' => $harian,
                'jumlah_truck' => $truck,
                'volume_borongan' => $vol,
                'upah_nominal' => $upahVal,
                'adm_nominal' => $adm,
                'toeslag_nominal' => $toeslag,
                'lembur_nominal' => $lembur,
                'jumlah_upah' => $subtotalBaris,
                'keterangan' => $ketArr[$i] ?? null,
            ];
        }

        $grandTotal = $subtotalUpah + $totalAdm + $totalToeslag + $totalLembur;
        $alokasiBuruh = floatval($request->input('alokasi_buruh', $subtotalUpah + $totalLembur));
        $alokasiKoperasi = floatval($request->input('alokasi_koperasi', $grandTotal - $alokasiBuruh));

        if (abs(($alokasiBuruh + $alokasiKoperasi) - $grandTotal) > 1) {
            return $this->jsonResponse(false, 'Total Alokasi Buruh (Rp ' . ribuan($alokasiBuruh) . ') + Koperasi (Rp ' . ribuan($alokasiKoperasi) . ') harus sama dengan Grand Total (Rp ' . ribuan($grandTotal) . '). Selisih: Rp ' . ribuan(abs(($alokasiBuruh + $alokasiKoperasi) - $grandTotal)));
        }

        $headerData = [
            'NO_KWITANSI' => $request->input('no_kwitansi') ?: $upah->NO_KWITANSI,
            'KOMODITAS' => $komoditas,
            'TGL_KEGIATAN_START' => $tglStart,
            'TGL_KEGIATAN_END' => $tglEnd,
            'TOTAL_TRUCK' => $totalTruck,
            'TOTAL_VOLUME' => $totalVolume,
            'SUBTOTAL_UPAH' => $subtotalUpah,
            'TOTAL_ADM' => $totalAdm,
            'TOTAL_TOESLAG' => $totalToeslag,
            'TOTAL_LEMBUR' => $totalLembur,
            'GRAND_TOTAL' => $grandTotal,
            'ALOKASI_BURUH' => $alokasiBuruh,
            'ALOKASI_KOPERASI' => $alokasiKoperasi,
            'REKENING_BANK' => $request->input('rekening_bank', $upah->REKENING_BANK),
            'KOORDINATOR_NAMA' => $request->input('koordinator_nama', $upah->KOORDINATOR_NAMA),
            'STATUS' => $request->input('status', $upah->STATUS),
            'CATATAN' => $request->input('catatan', $upah->CATATAN),
        ];

        try {
            $this->upahService->update((int)$id, $headerData, $details, auth()->id());
            return $this->jsonResponse(true, 'Perubahan berhasil disimpan');
        } catch (\Exception $e) {
            return $this->jsonResponse(false, 'Gagal memperbarui data: ' . $e->getMessage());
        }
    }

    public function delete(Request $request): JsonResponse
    {
        if (!$this->hasPerm($this->permissionDelete)) {
            return $this->jsonResponse(false, 'Anda tidak memiliki hak akses');
        }

        $id = $request->input('id');
        $upah = UpahTkbm::find($id);
        if (!$upah) {
            return $this->jsonResponse(false, 'Data tidak ditemukan');
        }

        if ($upah->STATUS === 'paid') {
            return $this->jsonResponse(false, 'Dokumen berstatus LUNAS tidak dapat dihapus');
        }

        $upah->delete();
        return $this->jsonResponse(true, 'Data berhasil dihapus');
    }

    public function restore(Request $request): JsonResponse
    {
        if (!$this->hasPerm($this->permissionUpdate)) {
            return $this->jsonResponse(false, 'Anda tidak memiliki hak akses');
        }

        $id = $request->input('id');
        $upah = UpahTkbm::onlyTrashed()->find($id);
        if (!$upah) {
            return $this->jsonResponse(false, 'Data tidak ditemukan');
        }

        $upah->restore();
        return $this->jsonResponse(true, 'Data berhasil dipulihkan');
    }

    public function updatePayment(Request $request): JsonResponse
    {
        if (!$this->hasPerm($this->permissionUpdate)) {
            return $this->jsonResponse(false, 'Anda tidak memiliki hak akses');
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'id_upah_tkbm' => 'required|numeric',
            'tanggal_bayar' => 'required|date',
            'bukti_bayar' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        if ($validator->fails()) {
            return $this->jsonResponse(false, $validator->errors()->first());
        }

        $id = $request->input('id_upah_tkbm');
        $upah = UpahTkbm::find($id);
        if (!$upah) {
            return $this->jsonResponse(false, 'Data tidak ditemukan');
        }

        $tglBayar = $request->input('tanggal_bayar', date('Y-m-d'));

        $buktiPath = null;
        if ($request->hasFile('bukti_bayar')) {
            $file = $request->file('bukti_bayar');
            $targetDir = public_path('filemanager/payment');
            if (!file_exists($targetDir)) {
                mkdir($targetDir, 0755, true);
            }
            $filename = 'upah_' . $id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move($targetDir, $filename);
            $buktiPath = 'filemanager/payment/' . $filename;
        }

        $success = $this->upahService->updatePayment((int)$id, $tglBayar, $buktiPath, auth()->id());
        if ($success) {
            return $this->jsonResponse(true, 'Pembayaran berhasil dikonfirmasi LUNAS');
        }
        return $this->jsonResponse(false, 'Gagal memperbarui status pembayaran');
    }

    public function cancel(Request $request): JsonResponse
    {
        if (!$this->hasPerm($this->permissionUpdate)) {
            return $this->jsonResponse(false, 'Anda tidak memiliki hak akses');
        }

        $id = $request->input('id_upah_tkbm');
        $upah = UpahTkbm::find($id);
        if (!$upah) {
            return $this->jsonResponse(false, 'Data tidak ditemukan');
        }

        if ($upah->STATUS === 'paid') {
            return $this->jsonResponse(false, 'Dokumen berstatus LUNAS tidak dapat dibatalkan');
        }

        $upah->update([
            'STATUS' => 'cancelled',
            'UPDATED_BY' => auth()->id(),
        ]);

        return $this->jsonResponse(true, 'Dokumen berhasil dibatalkan');
    }

    public function bridgeInvoice(Request $request): JsonResponse
    {
        if (!$this->hasPerm($this->permissionCreate)) {
            return $this->jsonResponse(false, 'Anda tidak memiliki hak akses');
        }

        $id = $request->input('id_upah_tkbm');
        try {
            $invoiceId = $this->upahService->bridgeToInvoice((int)$id, auth()->id());
            return $this->jsonResponse(true, 'Draft Invoice Komersial berhasil di-generate', [
                'invoice_id' => $invoiceId,
                'invoice_url' => url('accounting/invoice/detail/' . $invoiceId),
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse(false, 'Gagal generate invoice: ' . $e->getMessage());
        }
    }

    // -------------------------------------------------------------
    // PRINT VIEWS (3 DOKUMEN RESMI)
    // -------------------------------------------------------------

    public function printPerhitungan($id)
    {
        if (!$this->hasPerm($this->permissionRead)) {
            abort(403, 'Akses tidak diizinkan');
        }

        $upah = $this->upahService->getUpahTkbm((int)$id);
        if (!$upah) abort(404);

        $details = $this->upahService->getUpahTkbmDetails((int)$id);

        return view('accounting.upah.print_perhitungan', [
            'upah' => $upah,
            'details' => $details,
            'title' => 'Cetak Perhitungan Upah TKBM - ' . ($upah->NO_DOKUMEN),
        ]);
    }

    public function printTally($id)
    {
        if (!$this->hasPerm($this->permissionRead)) {
            abort(403, 'Akses tidak diizinkan');
        }

        $upah = $this->upahService->getUpahTkbm((int)$id);
        if (!$upah) abort(404);

        $details = $this->upahService->getUpahTkbmDetails((int)$id);

        return view('accounting.upah.print_tally', [
            'upah' => $upah,
            'details' => $details,
            'title' => 'Cetak Rekap Tally Truk - ' . ($upah->NO_DOKUMEN),
        ]);
    }

    public function printKwitansi($id)
    {
        if (!$this->hasPerm($this->permissionRead)) {
            abort(403, 'Akses tidak diizinkan');
        }

        $upah = $this->upahService->getUpahTkbm((int)$id);
        if (!$upah) abort(404);

        return view('accounting.upah.print_kwitansi', [
            'upah' => $upah,
            'title' => 'Cetak Kwitansi Resmi K.TKBM - ' . ($upah->NO_KWITANSI),
        ]);
    }
}

