<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Dermaga;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DermagaController extends Controller
{
    protected string $permissionRead   = 'read-master-dermaga';
    protected string $permissionCreate = 'create-master-dermaga';
    protected string $permissionUpdate = 'update-master-dermaga';
    protected string $permissionDelete = 'delete-master-dermaga';

    public function index()
    {
        $user = auth()->user();
        if ($user->group_id !== 1 && !Gate::allows($this->permissionRead)) {
            abort(403, 'Akses tidak diizinkan');
        }

        return view('master.dermaga.index', [
            'title' => 'Dermaga',
            'title_parent' => 'Master',
            'permission_create' => ($user->group_id === 1 || Gate::allows($this->permissionCreate)) ? 1 : 0,
        ]);
    }

    public function table(Request $request)
    {
        $showDeleted = (bool)$request->input('show_deleted');

        $query = Dermaga::with('pelabuhan');
        if ($showDeleted) {
            $query->onlyTrashed();
        }

        $items = $query->orderBy('ID_DERMAGA', 'DESC')->get();

        $data = [];
        $no = 1;
        $user = auth()->user();
        $canUpdate = $user->group_id === 1 || Gate::allows($this->permissionUpdate);
        $canDelete = $user->group_id === 1 || Gate::allows($this->permissionDelete);

        foreach ($items as $v) {
            $statusName = $v->STATUS == 1 ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-danger">Tidak Aktif</span>';

            if ($showDeleted) {
                $action = '<div class="hstack gap-2 justify-content-end">';
                if ($canDelete) {
                    $action .= '<button type="button" class="btn btn-sm btn-outline-success waves-effect waves-light btn-icon btn_restore" data-id="' . $v->ID_DERMAGA . '" data-name="' . e($v->NAMA) . '" title="Pulihkan"><i class="fal fa-undo"></i></button>';
                }
                $action .= '</div>';
            } else {
                $action = '<div class="hstack gap-2 justify-content-end">';
                if ($canUpdate) {
                    $action .= '<button type="button" class="btn btn-sm btn-outline-warning waves-effect waves-light btn-icon btn_edit" data-id="' . $v->ID_DERMAGA . '" title="Edit"><i class="fal fa-pencil"></i></button>';
                }
                if ($canDelete) {
                    $action .= '<button type="button" class="btn btn-sm btn-outline-danger waves-effect waves-light btn-icon btn_delete" data-id="' . $v->ID_DERMAGA . '" data-name="' . e($v->NAMA) . '" title="Hapus"><i class="fal fa-trash"></i></button>';
                }
                $action .= '</div>';
            }

            $data[] = [
                $no++,
                e($v->NAMA ?? '-'),
                e($v->pelabuhan->NAMA ?? '-'),
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
            return response()->json(['status' => false, 'msg' => 'Nama dermaga harus diisi']);
        }

        Dermaga::create([
            'NAMA' => $name,
            'ID_PELABUHAN' => $request->input('pelabuhan_id'),
            'STATUS' => (int)$request->input('status', 1),
            'CREATED_BY' => $user->id,
            'UPDATED_BY' => $user->id,
        ]);

        return response()->json(['status' => true, 'msg' => 'Data dermaga berhasil ditambahkan']);
    }

    public function edit(Request $request)
    {
        $id = $request->input('id');
        $model = Dermaga::with('pelabuhan')->find($id);

        if (!$model) {
            return '<div class="alert alert-danger">Data tidak ditemukan</div>';
        }

        return view('master.dermaga.edit', [
            'model' => $model,
            'title' => 'Dermaga',
        ]);
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        if ($user->group_id !== 1 && !Gate::allows($this->permissionUpdate)) {
            return response()->json(['status' => false, 'msg' => 'Tidak memiliki izin']);
        }

        $id = $request->input('model_id');
        $model = Dermaga::find($id);
        if (!$model) {
            return response()->json(['status' => false, 'msg' => 'Data tidak ditemukan']);
        }

        $name = trim($request->input('u_name', ''));
        $model->update([
            'NAMA' => !empty($name) ? $name : $model->NAMA,
            'ID_PELABUHAN' => $request->has('u_pelabuhan_id') ? $request->input('u_pelabuhan_id') : $model->ID_PELABUHAN,
            'STATUS' => $request->has('u_status') ? (int)$request->input('u_status') : $model->STATUS,
            'UPDATED_BY' => $user->id,
        ]);

        return response()->json([
            'status' => true,
            'msg' => 'Data dermaga berhasil diupdate',
            'url_back' => url('master/dermaga'),
        ]);
    }

    public function delete(Request $request)
    {
        $user = auth()->user();
        if ($user->group_id !== 1 && !Gate::allows($this->permissionDelete)) {
            return response()->json(['status' => false, 'msg' => 'Tidak memiliki izin']);
        }

        $id = $request->input('id');
        $model = Dermaga::find($id);
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
        $model = Dermaga::onlyTrashed()->find($id);
        if (!$model) {
            return response()->json(['status' => false, 'msg' => 'Data tidak ditemukan']);
        }

        $model->restore();

        return response()->json(['status' => true, 'msg' => 'Data berhasil dipulihkan']);
    }
}
