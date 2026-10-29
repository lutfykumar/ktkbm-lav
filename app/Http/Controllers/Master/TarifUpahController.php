<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Aktifitas;
use App\Models\Krk;
use App\Models\TarifUpah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TarifUpahController extends Controller
{
    protected string $permissionRead   = 'read-master-tarif_upah';
    protected string $permissionCreate = 'create-master-tarif_upah';
    protected string $permissionUpdate = 'update-master-tarif_upah';
    protected string $permissionDelete = 'delete-master-tarif_upah';

    private function hasPerm(string $permission): bool
    {
        $user = auth()->user();
        if (!$user) return false;
        if ($user->group_id === 1) return true;
        return Gate::allows($permission);
    }

    public function index()
    {
        if (!$this->hasPerm($this->permissionRead)) {
            abort(403, 'Akses tidak diizinkan');
        }

        $aktifitasList = Aktifitas::where('STATUS', 1)->orderBy('NAMA')->get();
        $krkList = Krk::where('STATUS', 1)->orderBy('NAMA')->get();

        return view('master.tarif_upah.index', [
            'title' => 'Tarif Upah TKBM',
            'title_parent' => 'Master Data',
            'permission_create' => $this->hasPerm($this->permissionCreate) ? 1 : 0,
            'aktifitasList' => $aktifitasList,
            'krkList' => $krkList,
        ]);
    }

    public function table(Request $request)
    {
        if (!$this->hasPerm($this->permissionRead)) {
            return response()->json([]);
        }

        $showDeleted = (bool)$request->input('show_deleted');

        $query = TarifUpah::with(['aktifitas', 'krk']);
        if ($showDeleted) {
            $query->onlyTrashed();
        }

        $items = $query->orderBy('ID_TARIF', 'DESC')->get();

        $data = [];
        $no = 1;
        $canUpdate = $this->hasPerm($this->permissionUpdate);
        $canDelete = $this->hasPerm($this->permissionDelete);

        foreach ($items as $v) {
            $statusName = $v->IS_ACTIVE ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-danger">Tidak Aktif</span>';

            $jenisBadge = match ($v->JENIS_TARIF) {
                'borongan' => '<span class="badge bg-primary">Borongan / Ton</span>',
                'harian' => '<span class="badge bg-info">Harian Shift</span>',
                'toeslag' => '<span class="badge bg-warning text-dark">Toeslag</span>',
                'adm_koperasi' => '<span class="badge bg-secondary">Adm Koperasi</span>',
                'lembur' => '<span class="badge bg-dark">Lembur</span>',
                default => '<span class="badge bg-light text-dark">' . ucfirst($v->JENIS_TARIF) . '</span>',
            };

            $detailInfo = '<strong>' . e($v->NAMA_TARIF) . '</strong>';
            if ($v->aktifitas) {
                $detailInfo .= '<br><small class="text-muted"><i class="fal fa-boxes me-1"></i> ' . e($v->aktifitas->NAMA) . '</small>';
            }
            if (!empty($v->KETERANGAN)) {
                $detailInfo .= '<br><small class="text-secondary">' . e($v->KETERANGAN) . '</small>';
            }

            if ($showDeleted) {
                $action = '<div class="hstack gap-2 justify-content-end">';
                if ($canDelete) {
                    $action .= '<button type="button" class="btn btn-sm btn-outline-success waves-effect waves-light btn-icon btn_restore" data-id="' . $v->ID_TARIF . '" data-name="' . e($v->NAMA_TARIF) . '" title="Pulihkan"><i class="fal fa-undo"></i></button>';
                }
                $action .= '</div>';
            } else {
                $action = '<div class="hstack gap-2 justify-content-end">';
                if ($canUpdate) {
                    $action .= '<button type="button" class="btn btn-sm btn-outline-warning waves-effect waves-light btn-icon btn_edit" data-id="' . $v->ID_TARIF . '" title="Edit"><i class="fal fa-pencil"></i></button>';
                }
                if ($canDelete) {
                    $action .= '<button type="button" class="btn btn-sm btn-outline-danger waves-effect waves-light btn-icon btn_delete" data-id="' . $v->ID_TARIF . '" data-name="' . e($v->NAMA_TARIF) . '" title="Hapus"><i class="fal fa-trash"></i></button>';
                }
                $action .= '</div>';
            }

            $data[] = [
                $no++,
                $detailInfo,
                $jenisBadge,
                '<strong>Rp ' . ribuan($v->NILAI_TARIF) . '</strong>',
                number_format(floatval($v->PERSEN_KAS_KOPERASI), 1, ',', '.') . ' %',
                $statusName,
                $action,
            ];
        }

        return response()->json($data);
    }

    public function store(Request $request)
    {
        if (!$this->hasPerm($this->permissionCreate)) {
            return response()->json(['status' => false, 'msg' => 'Tidak memiliki izin']);
        }

        $nama = trim($request->input('nama_tarif', ''));
        if (empty($nama)) {
            return response()->json(['status' => false, 'msg' => 'Nama Tarif harus diisi']);
        }

        $jenis = $request->input('jenis_tarif', 'borongan');
        $nilai = floatval(str_replace('.', '', $request->input('nilai_tarif', 0)));
        $persen = floatval($request->input('persen_kas_koperasi', 0));

        TarifUpah::create([
            'NAMA_TARIF' => $nama,
            'ID_AKTIFITAS' => $request->input('id_aktifitas') ?: null,
            'ID_KRK' => $request->input('id_krk') ?: null,
            'JENIS_TARIF' => $jenis,
            'NILAI_TARIF' => $nilai,
            'PERSEN_KAS_KOPERASI' => $persen,
            'KETERANGAN' => $request->input('keterangan'),
            'IS_ACTIVE' => (bool)$request->input('is_active', true),
        ]);

        return response()->json(['status' => true, 'msg' => 'Data Tarif Upah berhasil ditambahkan']);
    }

    public function edit(Request $request)
    {
        $id = $request->input('id');
        $model = TarifUpah::find($id);

        if (!$model) {
            return '<div class="alert alert-danger">Data tidak ditemukan</div>';
        }

        $aktifitasList = Aktifitas::where('STATUS', 1)->orderBy('NAMA')->get();
        $krkList = Krk::where('STATUS', 1)->orderBy('NAMA')->get();

        return view('master.tarif_upah.edit', [
            'model' => $model,
            'title' => 'Tarif Upah TKBM',
            'aktifitasList' => $aktifitasList,
            'krkList' => $krkList,
        ]);
    }

    public function update(Request $request)
    {
        if (!$this->hasPerm($this->permissionUpdate)) {
            return response()->json(['status' => false, 'msg' => 'Tidak memiliki izin']);
        }

        $id = $request->input('model_id');
        $model = TarifUpah::find($id);
        if (!$model) {
            return response()->json(['status' => false, 'msg' => 'Data tidak ditemukan']);
        }

        $nama = trim($request->input('u_nama_tarif', ''));
        if (empty($nama)) {
            return response()->json(['status' => false, 'msg' => 'Nama Tarif harus diisi']);
        }

        $model->update([
            'NAMA_TARIF' => $nama,
            'ID_AKTIFITAS' => $request->input('u_id_aktifitas') ?: null,
            'ID_KRK' => $request->input('u_id_krk') ?: null,
            'JENIS_TARIF' => $request->input('u_jenis_tarif', $model->JENIS_TARIF),
            'NILAI_TARIF' => floatval(str_replace('.', '', $request->input('u_nilai_tarif', $model->NILAI_TARIF))),
            'PERSEN_KAS_KOPERASI' => floatval($request->input('u_persen_kas_koperasi', $model->PERSEN_KAS_KOPERASI)),
            'KETERANGAN' => $request->input('u_keterangan', $model->KETERANGAN),
            'IS_ACTIVE' => $request->has('u_is_active') ? (bool)$request->input('u_is_active') : $model->IS_ACTIVE,
        ]);

        return response()->json([
            'status' => true,
            'msg' => 'Data Tarif Upah berhasil diupdate',
            'url_back' => url('master/tarif_upah'),
        ]);
    }

    public function delete(Request $request)
    {
        if (!$this->hasPerm($this->permissionDelete)) {
            return response()->json(['status' => false, 'msg' => 'Tidak memiliki izin']);
        }

        $id = $request->input('id');
        $model = TarifUpah::find($id);
        if (!$model) {
            return response()->json(['status' => false, 'msg' => 'Data tidak ditemukan']);
        }

        $model->delete();

        return response()->json(['status' => true, 'msg' => 'Data berhasil dihapus']);
    }

    public function restore(Request $request)
    {
        if (!$this->hasPerm($this->permissionDelete)) {
            return response()->json(['status' => false, 'msg' => 'Tidak memiliki izin']);
        }

        $id = $request->input('id');
        $model = TarifUpah::onlyTrashed()->find($id);
        if (!$model) {
            return response()->json(['status' => false, 'msg' => 'Data tidak ditemukan']);
        }

        $model->restore();

        return response()->json(['status' => true, 'msg' => 'Data berhasil dipulihkan']);
    }
}
