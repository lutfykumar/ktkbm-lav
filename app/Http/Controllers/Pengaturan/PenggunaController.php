<?php

namespace App\Http\Controllers\Pengaturan;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Services\PengaturanService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;

class PenggunaController extends Controller
{
    protected string $permissionRead   = 'read-advance-pengguna';
    protected string $permissionCreate = 'create-advance-pengguna';
    protected string $permissionUpdate = 'update-advance-pengguna';
    protected string $permissionDelete = 'delete-advance-pengguna';

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

        ActivityLogService::log('Lihat List Advanced Pengguna', 'view', 'advanced');

        return view('pengaturan.pengguna.index', [
            'title' => 'Pengguna',
            'title_parent' => 'Advanced',
            'permission_create' => $this->hasPerm($this->permissionCreate) ? 1 : 0,
            'permission_update' => $this->hasPerm($this->permissionUpdate) ? 1 : 0,
            'permission_delete' => $this->hasPerm($this->permissionDelete) ? 1 : 0,
        ]);
    }

    public function table(Request $request)
    {
        if (!$this->hasPerm($this->permissionRead)) {
            return response()->json([]);
        }

        $showDeleted = (bool)$request->input('show_deleted', false);
        $users = $this->service->getPenggunaTable($showDeleted);
        $data = [];
        $no = 1;

        foreach ($users as $v) {
            $btnId = $v->id;
            $btnName = $v->username;
            $action = '<div class="btn-group">';

            if ($showDeleted) {
                if ($this->hasPerm($this->permissionUpdate)) {
                    $action .= '<button type="button" class="btn btn-sm btn-success btn-icon waves-effect btn-restore"
                                    data-bs-toggle="tooltip" title="Restore Data" data-id="' . $btnId . '" data-title="' . e($btnName) . '">
                                <i class="fal fa-trash-restore"></i></button>';
                }
            } else {
                if ($this->hasPerm($this->permissionUpdate)) {
                    $action .= '<button type="button" class="btn btn-sm btn-info btn-icon waves-effect btn-change-password"
                                    data-bs-toggle="tooltip" title="Ubah Password" data-id="' . $btnId . '" data-username="' . e($btnName) . '">
                                <i class="fal fa-key"></i></button>';

                    $action .= '<button type="button" class="btn btn-sm btn-warning btn-icon waves-effect btn-edit"
                                    data-bs-toggle="tooltip" title="Edit Data" data-id="' . $btnId . '" data-title="' . e($btnName) . '">
                                <i class="fal fa-pen"></i></button>';
                }

                if ($this->hasPerm($this->permissionDelete) && $btnId != auth()->id()) {
                    $action .= '<button type="button" class="btn btn-sm btn-danger btn-icon waves-effect btn-delete"
                                    data-bs-toggle="tooltip" title="Hapus Data" data-id="' . $btnId . '" data-title="' . e($btnName) . '">
                                <i class="fal fa-trash-alt"></i></button>';
                }
            }

            $action .= '</div>';

            $data[] = [
                $no,
                e($v->username ?? '-'),
                e($v->email ?? '-'),
                e($v->role_name ?? '-'),
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
        $email = trim($request->input('email', ''));
        $password = $request->input('password', '');
        $roles = $request->input('roles');

        if (empty($name)) {
            return $this->jsonResponse(false, 'Nama pengguna harus diisi');
        }

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->jsonResponse(false, 'Format email tidak valid');
        }

        if (empty($password)) {
            return $this->jsonResponse(false, 'Password harus diisi');
        }

        if (empty($roles)) {
            return $this->jsonResponse(false, 'Role harus dipilih');
        }

        // Check duplicate email
        $exists = DB::table('s_users')->where('email', $email)->exists();
        if ($exists) {
            return $this->jsonResponse(false, 'Email sudah terdaftar');
        }

        try {
            DB::beginTransaction();

            $now = time();
            $userId = DB::table('s_users')->insertGetId([
                'ip_address' => $request->ip() ?? '127.0.0.1',
                'username' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'active' => 1,
                'created_on' => $now,
                'last_login' => $now,
                'CREATED_AT' => now(),
                'UPDATED_AT' => now(),
            ]);

            DB::table('s_users_groups')->insert([
                'user_id' => $userId,
                'group_id' => (int)$roles,
                'CREATED_AT' => now(),
                'UPDATED_AT' => now(),
            ]);

            DB::commit();

            ActivityLogService::log('Menambah Data Pengguna: ' . $name, 'create', 'advanced', [
                'affected_record_id' => $userId,
                'affected_table' => 's_users',
            ]);

            return $this->jsonResponse(true, 'Data pengguna berhasil ditambahkan');
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

        $model = $this->service->getPengguna((int)$id);
        if (!$model) {
            return '<div class="alert alert-danger">Data tidak ditemukan</div>';
        }

        return view('pengaturan.pengguna.edit', [
            'model' => $model,
            'title' => 'Pengguna',
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        if (!$this->hasPerm($this->permissionUpdate)) {
            return $this->jsonResponse(false, 'Anda tidak memiliki hak akses');
        }

        $id = $request->input('model_id');
        $uName = trim($request->input('u_name', ''));
        $uEmail = trim($request->input('u_email', ''));
        $uRoles = $request->input('u_roles');

        if (empty($id) || empty($uName)) {
            return $this->jsonResponse(false, 'Nama pengguna harus diisi');
        }

        if (empty($uEmail) || !filter_var($uEmail, FILTER_VALIDATE_EMAIL)) {
            return $this->jsonResponse(false, 'Format email tidak valid');
        }

        if (empty($uRoles)) {
            return $this->jsonResponse(false, 'Role harus dipilih');
        }

        // Check duplicate email (exclude self)
        $exists = DB::table('s_users')->where('email', $uEmail)->where('id', '!=', $id)->exists();
        if ($exists) {
            return $this->jsonResponse(false, 'Email sudah terdaftar');
        }

        try {
            DB::beginTransaction();

            DB::table('s_users')->where('id', $id)->update([
                'username' => $uName,
                'email' => $uEmail,
                'UPDATED_AT' => now(),
            ]);

            // Update or create user group
            $hasGroup = DB::table('s_users_groups')->where('user_id', $id)->exists();
            if ($hasGroup) {
                DB::table('s_users_groups')->where('user_id', $id)->update([
                    'group_id' => (int)$uRoles,
                    'UPDATED_AT' => now(),
                ]);
            } else {
                DB::table('s_users_groups')->insert([
                    'user_id' => $id,
                    'group_id' => (int)$uRoles,
                    'CREATED_AT' => now(),
                    'UPDATED_AT' => now(),
                ]);
            }

            DB::commit();

            ActivityLogService::log('Mengupdate Data Pengguna: ' . $uName, 'update', 'advanced', [
                'affected_record_id' => $id,
                'affected_table' => 's_users',
            ]);

            return $this->jsonResponse(true, 'Data pengguna berhasil diupdate');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->jsonResponse(false, 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    public function changePassword(Request $request): JsonResponse
    {
        if (!$this->hasPerm($this->permissionUpdate)) {
            return $this->jsonResponse(false, 'Anda tidak memiliki hak akses');
        }

        $userId = $request->input('user_id');
        $newPassword = $request->input('new_password');
        $confirmPassword = $request->input('confirm_password');

        if (empty($userId)) {
            return $this->jsonResponse(false, 'User ID tidak valid');
        }

        if (empty($newPassword) || strlen($newPassword) < 6) {
            return $this->jsonResponse(false, 'Password baru minimal 6 karakter');
        }

        if ($newPassword !== $confirmPassword) {
            return $this->jsonResponse(false, 'Password dan konfirmasi password tidak sama');
        }

        $user = DB::table('s_users')->where('id', $userId)->first();
        if (!$user) {
            return $this->jsonResponse(false, 'Data user tidak ditemukan');
        }

        try {
            DB::table('s_users')->where('id', $userId)->update([
                'password' => Hash::make($newPassword),
                'UPDATED_AT' => now(),
            ]);

            ActivityLogService::log('Change Password untuk Pengguna: ' . $user->username, 'update', 'advanced', [
                'affected_record_id' => $userId,
                'affected_table' => 's_users',
            ]);

            return $this->jsonResponse(true, 'Password berhasil diubah');
        } catch (\Exception $e) {
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

        if ($id == auth()->id()) {
            return $this->jsonResponse(false, 'Tidak dapat menghapus akun Anda sendiri');
        }

        $user = DB::table('s_users')->where('id', $id)->first();
        if (!$user) {
            return $this->jsonResponse(false, 'Data tidak ditemukan');
        }

        try {
            DB::beginTransaction();

            DB::table('s_users_groups')->where('user_id', $id)->update(['DELETED_AT' => now()]);
            DB::table('s_users')->where('id', $id)->update(['DELETED_AT' => now()]);

            DB::commit();

            ActivityLogService::log('Menghapus Data Pengguna: ' . $user->username, 'delete', 'advanced', [
                'affected_record_id' => $id,
                'affected_table' => 's_users',
            ]);

            return $this->jsonResponse(true, 'Data berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->jsonResponse(false, 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    public function restore(Request $request): JsonResponse
    {
        if (!$this->hasPerm($this->permissionUpdate)) {
            return $this->jsonResponse(false, 'Anda tidak memiliki hak akses');
        }

        $id = $request->input('id');
        if (empty($id)) {
            return $this->jsonResponse(false, 'ID tidak valid');
        }

        $user = DB::table('s_users')->where('id', $id)->first();
        if (!$user) {
            return $this->jsonResponse(false, 'Data tidak ditemukan');
        }

        try {
            DB::beginTransaction();

            DB::table('s_users_groups')->where('user_id', $id)->update(['DELETED_AT' => null]);
            DB::table('s_users')->where('id', $id)->update(['DELETED_AT' => null]);

            DB::commit();

            ActivityLogService::log('Memulihkan Data Pengguna: ' . $user->username, 'update', 'advanced', [
                'affected_record_id' => $id,
                'affected_table' => 's_users',
            ]);

            return $this->jsonResponse(true, 'Data berhasil dipulihkan');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->jsonResponse(false, 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }
}
