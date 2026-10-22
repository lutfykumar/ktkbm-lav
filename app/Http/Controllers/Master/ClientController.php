<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ClientController extends Controller
{
    protected string $permissionRead   = 'read-master-client';
    protected string $permissionCreate = 'create-master-client';
    protected string $permissionUpdate = 'update-master-client';
    protected string $permissionDelete = 'delete-master-client';

    public function index()
    {
        $user = auth()->user();
        if ($user->group_id !== 1 && !Gate::allows($this->permissionRead)) {
            abort(403, 'Akses tidak diizinkan');
        }

        return view('master.client.index', [
            'title' => 'Client',
            'title_parent' => 'Master',
            'permission_create' => ($user->group_id === 1 || Gate::allows($this->permissionCreate)) ? 1 : 0,
        ]);
    }

    public function table(Request $request)
    {
        $showDeleted = (bool)$request->input('show_deleted');

        $query = Client::query();
        if ($showDeleted) {
            $query->onlyTrashed();
        }

        $items = $query->orderBy('ID_CLIENT', 'DESC')->get();

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
                    $action .= '<button type="button" class="btn btn-sm btn-outline-success waves-effect waves-light btn-icon btn_restore" data-id="' . $v->ID_CLIENT . '" data-name="' . e($v->NAMA) . '" title="Pulihkan"><i class="fal fa-undo"></i></button>';
                }
                $action .= '</div>';
            } else {
                $action = '<div class="hstack gap-2 justify-content-end">';
                if ($canUpdate) {
                    $action .= '<button type="button" class="btn btn-sm btn-outline-warning waves-effect waves-light btn-icon btn_edit" data-id="' . $v->ID_CLIENT . '" title="Edit"><i class="fal fa-pencil"></i></button>';
                }
                if ($canDelete) {
                    $action .= '<button type="button" class="btn btn-sm btn-outline-danger waves-effect waves-light btn-icon btn_delete" data-id="' . $v->ID_CLIENT . '" data-name="' . e($v->NAMA) . '" title="Hapus"><i class="fal fa-trash"></i></button>';
                }
                $action .= '</div>';
            }

            $data[] = [
                $no++,
                e($v->NAMA ?? '-'),
                e($v->SHORT ?? '-'),
                e($v->ALAMAT ?? '-'),
                e($v->KONTAK ?? '-'),
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
            return response()->json(['status' => false, 'msg' => 'Nama client harus diisi']);
        }

        Client::create([
            'NAMA' => $name,
            'SHORT' => $request->input('short', ''),
            'ALAMAT' => $request->input('alamat', ''),
            'KONTAK' => $request->input('kontak', ''),
            'STATUS' => (int)$request->input('status', 1),
            'CREATED_BY' => $user->id,
            'UPDATED_BY' => $user->id,
        ]);

        return response()->json(['status' => true, 'msg' => 'Data client berhasil ditambahkan']);
    }

    public function edit(Request $request)
    {
        $id = $request->input('id');
        $model = Client::find($id);

        if (!$model) {
            return '<div class="alert alert-danger">Data tidak ditemukan</div>';
        }

        return view('master.client.edit', [
            'model' => $model,
            'title' => 'Client',
        ]);
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        if ($user->group_id !== 1 && !Gate::allows($this->permissionUpdate)) {
            return response()->json(['status' => false, 'msg' => 'Tidak memiliki izin']);
        }

        $id = $request->input('model_id');
        $model = Client::find($id);
        if (!$model) {
            return response()->json(['status' => false, 'msg' => 'Data tidak ditemukan']);
        }

        $name = trim($request->input('u_name', ''));
        $model->update([
            'NAMA' => !empty($name) ? $name : $model->NAMA,
            'SHORT' => $request->has('u_short') ? $request->input('u_short') : $model->SHORT,
            'ALAMAT' => $request->has('u_alamat') ? $request->input('u_alamat') : $model->ALAMAT,
            'KONTAK' => $request->has('u_kontak') ? $request->input('u_kontak') : $model->KONTAK,
            'STATUS' => $request->has('u_status') ? (int)$request->input('u_status') : $model->STATUS,
            'UPDATED_BY' => $user->id,
        ]);

        return response()->json([
            'status' => true,
            'msg' => 'Data client berhasil diupdate',
            'url_back' => url('master/client'),
        ]);
    }

    public function delete(Request $request)
    {
        $user = auth()->user();
        if ($user->group_id !== 1 && !Gate::allows($this->permissionDelete)) {
            return response()->json(['status' => false, 'msg' => 'Tidak memiliki izin']);
        }

        $id = $request->input('id');
        $model = Client::find($id);
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
        $model = Client::onlyTrashed()->find($id);
        if (!$model) {
            return response()->json(['status' => false, 'msg' => 'Data tidak ditemukan']);
        }

        $model->restore();

        return response()->json(['status' => true, 'msg' => 'Data berhasil dipulihkan']);
    }
}
