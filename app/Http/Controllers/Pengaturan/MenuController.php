<?php

namespace App\Http\Controllers\Pengaturan;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogService;
use App\Services\SidebarService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class MenuController extends Controller
{
    protected string $permissionRead   = 'read-advance-pengaturan-menu';
    protected string $permissionUpdate = 'update-advance-pengaturan-menu';

    protected SidebarService $sidebarService;

    public function __construct(SidebarService $sidebarService)
    {
        $this->sidebarService = $sidebarService;
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

        ActivityLogService::log('Lihat List Pengaturan Menu', 'view', 'pengaturan');

        $sidebar = $this->sidebarService->getSidebarMenu();

        return view('pengaturan.menu.index', [
            'title' => 'Menu',
            'title_parent' => 'Pengaturan',
            'sidebar' => $sidebar,
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        if (!$this->hasPerm($this->permissionUpdate)) {
            return $this->jsonResponse(false, 'Anda tidak memiliki hak akses');
        }

        $menusRaw = $request->input('menus');
        if (empty($menusRaw)) {
            return $this->jsonResponse(false, 'Data menu tidak boleh kosong');
        }

        $menus = is_string($menusRaw) ? json_decode($menusRaw, true) : $menusRaw;
        if (!is_array($menus)) {
            return $this->jsonResponse(false, 'Format data menu tidak valid');
        }

        try {
            DB::beginTransaction();

            foreach ($menus as $item) {
                if (!empty($item['menu_id'])) {
                    DB::table('s_menu')->where('ID_MENU', $item['menu_id'])->update([
                        'URUT' => (int)($item['urut'] ?? 0),
                        'UPDATED_AT' => now(),
                    ]);
                }
            }

            DB::commit();

            ActivityLogService::log('Mengupdate Pengaturan Menu Location', 'update', 'pengaturan');

            return $this->jsonResponse(true, 'Urutan menu berhasil diperbarui');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->jsonResponse(false, 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }
}
