<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Aktifitas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AktifitasController extends Controller
{
    protected string $permissionRead   = 'read-master-jenis-aktifitas';
    protected string $permissionCreate = 'create-master-jenis-aktifitas';
    protected string $permissionUpdate = 'update-master-jenis-aktifitas';
    protected string $permissionDelete = 'delete-master-jenis-aktifitas';

    public function index()
    {
        $user = auth()->user();
        if ($user->group_id !== 1 && !Gate::allows($this->permissionRead)) {
            abort(403, 'Akses tidak diizinkan');
        }

        $canCreate = $user->group_id === 1 || Gate::allows($this->permissionCreate);

        return view('master.aktifitas.index', [
            'title' => 'Aktifitas',
            'title_parent' => 'Master',
            'permission_create' => $canCreate ? 1 : 0,
        ]);
    }

    public function table(Request $request)
    {
        $showDeleted = (bool)$request->input('show_deleted');

        $query = Aktifitas::with('satuan');
        if ($showDeleted) {
            $query->onlyTrashed();
        }

        $items = $query->orderBy('ID_AKTIFITAS', 'DESC')->get();

        $data = [];
        $no = 1;
        $user = auth()->user();
        $canUpdate = $user->group_id === 1 || Gate::allows($this->permissionUpdate);
        $canDelete = $user->group_id === 1 || Gate::allows($this->permissionDelete);

        foreach ($items as $v) {
            $tipeBadge = (!empty($v->TIPE) && $v->TIPE === 'muat')
                ? '<span class="badge bg-primary">Muat</span>'
                : '<span class="badge bg-info">Bongkar</span>';

            $statusName = $v->STATUS == 1 ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-danger">Tidak Aktif</span>';

            if ($showDeleted) {
                $action = '<div class="hstack gap-2 justify-content-end">';
                if ($canDelete) {
                    $action .= '<button type="button" class="btn btn-sm btn-outline-success waves-effect waves-light btn-icon btn_restore" data-id="' . $v->ID_AKTIFITAS . '" data-name="' . e($v->NAMA) . '" title="Pulihkan"><i class="fal fa-undo"></i></button>';
                }
                $action .= '</div>';
            } else {
                $action = '<div class="hstack gap-2 justify-content-end">';
                if ($canUpdate) {
                    $action .= '<button type="button" class="btn btn-sm btn-outline-warning waves-effect waves-light btn-icon btn_edit" data-id="' . $v->ID_AKTIFITAS . '" title="Edit"><i class="fal fa-pencil"></i></button>';
                }
                if ($canDelete) {
                    $action .= '<button type="button" class="btn btn-sm btn-outline-danger waves-effect waves-light btn-icon btn_delete" data-id="' . $v->ID_AKTIFITAS . '" data-name="' . e($v->NAMA) . '" title="Hapus"><i class="fal fa-trash"></i></button>';
                }
                $action .= '</div>';
            }

            $data[] = [
                $no++,
                e($v->NAMA ?? '-'),
                $tipeBadge,
                e($v->satuan->NAMA ?? '-'),
                $statusName,
                $action,
            ];
        }

        return response()->json($data);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        if ($user->group_id !== 1 && !Gate::allows($this->permissionCreate)) {
            return response()->json(['status' => false, 'msg' => 'Tidak memiliki izin']);
        }

        $name = trim($request->input('name', ''));
        if (empty($name)) {
            return response()->json(['status' => false, 'msg' => 'Nama aktifitas harus diisi']);
        }

        $tipe = $request->input('tipe');
        if (!in_array($tipe, ['bongkar', 'muat'])) {
            $tipe = 'bongkar';
        }

        $satuanId = $request->input('satuan_id');
        $status = (int)$request->input('status', 1);

        Aktifitas::create([
            'NAMA' => $name,
            'TIPE' => $tipe,
            'ID_SATUAN' => $satuanId ?: null,
            'STATUS' => $status,
            'CREATED_BY' => $user->id,
            'UPDATED_BY' => $user->id,
        ]);

        return response()->json(['status' => true, 'msg' => 'Data aktifitas berhasil ditambahkan']);
    }

    public function edit(Request $request)
    {
        $id = $request->input('id');
        $model = Aktifitas::with('satuan')->find($id);

        if (!$model) {
            return '<div class="alert alert-danger">Data tidak ditemukan</div>';
        }

        return view('master.aktifitas.edit', [
            'model' => $model,
            'title' => 'Aktifitas',
        ]);
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        if ($user->group_id !== 1 && !Gate::allows($this->permissionUpdate)) {
            return response()->json(['status' => false, 'msg' => 'Tidak memiliki izin']);
        }

        $id = $request->input('model_id');
        $model = Aktifitas::find($id);
        if (!$model) {
            return response()->json(['status' => false, 'msg' => 'Data tidak ditemukan']);
        }

        $name = trim($request->input('u_name', ''));
        $tipe = $request->input('u_tipe', $model->TIPE);
        $satuanId = $request->input('u_satuan_id', $model->ID_SATUAN);
        $status = $request->has('u_status') ? (int)$request->input('u_status') : $model->STATUS;

        $model->update([
            'NAMA' => !empty($name) ? $name : $model->NAMA,
            'TIPE' => in_array($tipe, ['bongkar', 'muat']) ? $tipe : $model->TIPE,
            'ID_SATUAN' => $satuanId ?: null,
            'STATUS' => $status,
            'UPDATED_BY' => $user->id,
        ]);

        return response()->json([
            'status' => true,
            'msg' => 'Data aktifitas berhasil diupdate',
            'url_back' => url('master/aktifitas'),
        ]);
    }

    public function delete(Request $request)
    {
        $user = auth()->user();
        if ($user->group_id !== 1 && !Gate::allows($this->permissionDelete)) {
            return response()->json(['status' => false, 'msg' => 'Tidak memiliki izin']);
        }

        $id = $request->input('id');
        $model = Aktifitas::find($id);
        if (!$model) {
            return response()->json(['status' => false, 'msg' => 'Data tidak ditemukan']);
        }

        $model->delete();

        return response()->json(['status' => true, 'msg' => 'Data berhasil dihapus']);
    }

    public function restore(Request $request)
    {
        $user = auth()->user();
        if ($user->group_id !== 1 && !Gate::allows($this->permissionDelete)) {
            return response()->json(['status' => false, 'msg' => 'Tidak memiliki izin']);
        }

        $id = $request->input('id');
        $model = Aktifitas::onlyTrashed()->find($id);
        if (!$model) {
            return response()->json(['status' => false, 'msg' => 'Data tidak ditemukan']);
        }

        $model->restore();

        return response()->json(['status' => true, 'msg' => 'Data berhasil dipulihkan']);
    }
}
