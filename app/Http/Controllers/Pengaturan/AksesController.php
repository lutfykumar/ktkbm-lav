<?php

namespace App\Http\Controllers\Pengaturan;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogService;
use App\Services\PengaturanService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class AksesController extends Controller
{
    protected string $permissionRead   = 'read-advance-pengaturan-akses';
    protected string $permissionCreate = 'create-advance-pengaturan-akses';
    protected string $permissionUpdate = 'update-advance-pengaturan-akses';
    protected string $permissionDelete = 'delete-advance-pengaturan-akses';

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

        ActivityLogService::log('Lihat List Pengaturan Menu Akses', 'view', 'pengaturan');

        return view('pengaturan.akses.index', [
            'title' => 'Menu Akses',
            'title_parent' => 'Pengaturan',
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

        $items = $this->service->getAksesTable();
        $data = [];
        $no = 1;

        foreach ($items as $v) {
            $btnId = $v->ID_MENU;
            $btnName = $v->NAMA;
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

            $statusBadge = $v->TAMPIL
                ? '<span class="badge bg-success">Aktif</span>'
                : '<span class="badge bg-danger">Tidak Aktif</span>';

            $data[] = [
                $no,
                e($v->NAMA ?? '-'),
                e($v->HEADER_NAMA ?? '-'),
                e($v->PARENT_NAMA ?? '-'),
                e($v->ICON ?? '-'),
                e($v->LINK ?? '-'),
                $v->URUT ?? 0,
                $statusBadge,
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
        $header = $request->input('header');
        $parent = $request->input('parent');
        $icon = trim($request->input('icon', ''));
        $link = trim($request->input('link', ''));
        $urutan = (int)$request->input('urutan', 0);
        $status = (int)$request->input('status', 1);

        if (empty($name)) {
            return $this->jsonResponse(false, 'Nama menu harus diisi');
        }

        try {
            DB::beginTransaction();

            $slug = Str::slug($name);

            $insertData = [
                'CREATED_BY' => auth()->id(),
                'NAMA' => $name,
                'SLUG' => $slug,
                'HEADER_ID' => !empty($header) ? (int)$header : null,
                'PARENT_ID' => !empty($parent) ? (int)$parent : null,
                'ICON' => !empty($icon) ? $icon : null,
                'LINK' => !empty($link) ? $link : null,
                'URUT' => $urutan,
                'TAMPIL' => $status,
                'CREATED_AT' => now(),
                'UPDATED_AT' => now(),
            ];

            $menuId = DB::table('s_menu')->insertGetId($insertData);

            // Auto-create standard CRUD permissions for this menu
            $permTypes = ['Read', 'Create', 'Update', 'Delete'];
            foreach ($permTypes as $i => $type) {
                DB::table('s_permission')->insert([
                    'ID_MENU' => $menuId,
                    'NAMA' => $type . ' ' . $name,
                    'SLUG' => strtolower($type) . '-' . $slug,
                    'URUT' => $i + 1,
                ]);
            }

            DB::commit();

            ActivityLogService::log('Menambah Data Menu Akses: ' . $name, 'create', 'pengaturan', [
                'affected_record_id' => $menuId,
                'affected_table' => 's_menu',
            ]);

            return $this->jsonResponse(true, 'Data Berhasil disimpan');
        } catch (\Exception $e) {
            DB::rollBack();
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

        $model = $this->service->getAkses((int)$id);
        if (!$model) {
            return '<div class="alert alert-danger">Data tidak ditemukan</div>';
        }

        return view('pengaturan.akses.edit', [
            'model' => $model,
            'title' => 'Menu Akses',
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        if (!$this->hasPerm($this->permissionUpdate)) {
            return $this->jsonResponse(false, 'Anda tidak memiliki hak akses');
        }

        $id = $request->input('id');
        $name = trim($request->input('u_name', ''));
        $header = $request->input('u_header');
        $parent = $request->input('u_parent');
        $icon = trim($request->input('u_icon', ''));
        $link = trim($request->input('u_link', ''));
        $urutan = (int)$request->input('u_urutan', 0);
        $status = (int)$request->input('u_status', 1);

        if (empty($id) || empty($name)) {
            return $this->jsonResponse(false, 'Nama menu harus diisi');
        }

        $menu = $this->service->getAkses((int)$id);
        if (!$menu) {
            return $this->jsonResponse(false, 'Data menu tidak ditemukan');
        }

        try {
            DB::beginTransaction();

            $updateData = [
                'NAMA' => $name,
                'HEADER_ID' => !empty($header) ? (int)$header : null,
                'PARENT_ID' => !empty($parent) ? (int)$parent : null,
                'ICON' => !empty($icon) ? $icon : null,
                'LINK' => !empty($link) ? $link : null,
                'URUT' => $urutan,
                'TAMPIL' => $status,
                'UPDATED_AT' => now(),
            ];

            DB::table('s_menu')->where('ID_MENU', $id)->update($updateData);

            DB::commit();

            ActivityLogService::log('Mengupdate Data Menu Akses: ' . $name, 'update', 'pengaturan', [
                'affected_record_id' => $id,
                'affected_table' => 's_menu',
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

        $menu = $this->service->getAkses((int)$id);
        if (!$menu) {
            return $this->jsonResponse(false, 'Data tidak ditemukan');
        }

        try {
            DB::beginTransaction();

            DB::table('s_group_permissions')->whereIn('ID_PERMISSION', function ($q) use ($id) {
                $q->select('ID_PERMISSION')->from('s_permission')->where('ID_MENU', $id);
            })->delete();

            DB::table('s_permission')->where('ID_MENU', $id)->delete();
            DB::table('s_group_menu')->where('ID_MENU', $id)->delete();
            DB::table('s_menu')->where('ID_MENU', $id)->delete();

            DB::commit();

            ActivityLogService::log('Menghapus Data Menu Akses: ' . $menu->NAMA, 'delete', 'pengaturan', [
                'affected_record_id' => $id,
                'affected_table' => 's_menu',
            ]);

            return $this->jsonResponse(true, 'Data Berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->jsonResponse(false, 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }
}
