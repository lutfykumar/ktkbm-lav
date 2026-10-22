<?php

namespace App\Http\Controllers\Pengaturan;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Services\ActivityLogService;
use App\Services\PengaturanService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class RolesController extends Controller
{
    protected string $permissionRead   = 'read-advance-pengaturan-roles';
    protected string $permissionCreate = 'create-advance-pengaturan-roles';
    protected string $permissionUpdate = 'update-advance-pengaturan-roles';
    protected string $permissionDelete = 'delete-advance-pengaturan-roles';

    protected PengaturanService $service;

    public function __construct(PengaturanService $service)
    {
        $this->service = $service;
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

        ActivityLogService::log('Lihat List Advanced Jabatan', 'view', 'advanced');

        return view('pengaturan.roles.index', [
            'title' => 'Advanced Jabatan',
            'title_parent' => 'Advanced',
            'permission_create' => $this->hasPerm($this->permissionCreate) ? 1 : 0,
            'permission_update' => $this->hasPerm($this->permissionUpdate) ? 1 : 0,
            'permission_delete' => $this->hasPerm($this->permissionDelete) ? 1 : 0,
        ]);
    }

    public function table()
    {
        if (!$this->hasPerm($this->permissionRead)) {
            return response()->json([]);
        }

        $roles = $this->service->getRolesTable();
        $data = [];
        $no = 1;

        foreach ($roles as $v) {
            $btnId = $v->id;
            $btnName = $v->name;
            $action = '<div class="btn-group">';

            if ($this->hasPerm($this->permissionUpdate)) {
                $action .= '<button type="button" class="btn btn-sm btn-warning btn-icon waves-effect btn-edit"
                                data-bs-toggle="tooltip" title="Edit Data" data-id="' . $btnId . '" data-title="' . e($btnName) . '">
                            <i class="fal fa-pen"></i></button>';
            }

            if ($this->hasPerm($this->permissionDelete)) {
                $action .= '<button type="button" class="btn btn-sm btn-danger btn-icon waves-effect btn-delete"
                                data-bs-toggle="tooltip" title="Hapus Data" data-id="' . $btnId . '" data-title="' . e($btnName) . '">
                            <i class="fal fa-trash-alt"></i></button>';
            }

            $action .= '</div>';

            $data[] = [
                $no,
                e($v->name ?? '-'),
                e($v->description ?? '-'),
                $action,
            ];
            $no++;
        }

        return response()->json($data);
    }

    public function store(Request $request): JsonResponse
    {
        if (!$this->hasPerm($this->permissionCreate)) {
            return $this->jsonResponse(false, 'Anda tidak memiliki hak akses');
        }

        $name = trim($request->input('name', ''));
        $body = trim($request->input('body', ''));

        if (empty($name)) {
            return $this->jsonResponse(false, 'Nama jabatan harus diisi');
        }

        try {
            $insertData = [
                'name' => $name,
                'description' => $body,
                'CREATED_AT' => now(),
                'UPDATED_AT' => now(),
            ];

            $id = DB::table('s_groups')->insertGetId($insertData);

            ActivityLogService::log('Menambah Data Jabatan: ' . $name, 'create', 'advanced', [
                'affected_record_id' => $id,
                'affected_table' => 's_groups',
                'new_values' => $insertData,
            ]);

            return $this->jsonResponse(true, 'Data Berhasil disimpan');
        } catch (\Exception $e) {
            return $this->jsonResponse(false, 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    public function edit(Request $request)
    {
        if (!$this->hasPerm($this->permissionUpdate)) {
            return '<div class="alert alert-danger">Akses ditolak</div>';
        }

        $id = $request->input('id');
        if (empty($id)) {
            return '<div class="alert alert-danger">ID tidak valid</div>';
        }

        $model = $this->service->getRole((int)$id);
        if (!$model) {
            return '<div class="alert alert-danger">Data tidak ditemukan</div>';
        }

        $allMenuAccess = $this->service->allMenuAccess();
        $userAccessPermissions = $this->service->getRoleActivePermissions((int)$id);

        return view('pengaturan.roles.edit', [
            'model' => $model,
            'title' => 'Roles',
            'all_menu_access' => $allMenuAccess,
            'user_access_permissions' => $userAccessPermissions,
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        if (!$this->hasPerm($this->permissionUpdate)) {
            return $this->jsonResponse(false, 'Anda tidak memiliki hak akses');
        }

        $id = $request->input('model_id');
        $name = trim($request->input('u_name', ''));
        $body = trim($request->input('u_body', ''));
        $permissions = $request->input('permissions', []);

        if (empty($id) || empty($name)) {
            return $this->jsonResponse(false, 'Nama jabatan harus diisi');
        }

        $role = $this->service->getRole((int)$id);
        if (!$role) {
            return $this->jsonResponse(false, 'Data role tidak ditemukan');
        }

        try {
            DB::beginTransaction();

            DB::table('s_groups')->where('id', $id)->update([
                'name' => $name,
                'description' => $body,
                'UPDATED_AT' => now(),
            ]);

            $this->service->updateRolePermissionsAndMenus((int)$id, (array)$permissions);

            DB::commit();

            ActivityLogService::log('Mengupdate Data Roles: ' . $name, 'update', 'advanced', [
                'affected_record_id' => $id,
                'affected_table' => 's_groups',
            ]);

            return $this->jsonResponse(true, 'Data Berhasil diupdate');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->jsonResponse(false, 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    public function delete(Request $request): JsonResponse
    {
        if (!$this->hasPerm($this->permissionDelete)) {
            return $this->jsonResponse(false, 'Anda tidak memiliki hak akses');
        }

        $id = $request->input('id');
        if (empty($id)) {
            return $this->jsonResponse(false, 'ID tidak valid');
        }

        if ($id == 1) {
            return $this->jsonResponse(false, 'Role Administrator utama tidak dapat dihapus');
        }

        $role = $this->service->getRole((int)$id);
        if (!$role) {
            return $this->jsonResponse(false, 'Data tidak ditemukan');
        }

        try {
            DB::beginTransaction();

            DB::table('s_users_groups')->where('group_id', $id)->update(['DELETED_AT' => now()]);
            DB::table('s_groups')->where('id', $id)->update(['DELETED_AT' => now()]);

            DB::commit();

            ActivityLogService::log('Menghapus Data Roles: ' . $role->name, 'delete', 'advanced', [
                'affected_record_id' => $id,
                'affected_table' => 's_groups',
            ]);

            return $this->jsonResponse(true, 'Data Berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->jsonResponse(false, 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }
}
