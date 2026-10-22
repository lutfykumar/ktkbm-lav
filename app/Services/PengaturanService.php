<?php

namespace App\Services;

use App\Models\Group;
use App\Models\Menu;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PengaturanService
{
    // ==========================================
    // ROLES
    // ==========================================

    public function getRolesTable(): array
    {
        return DB::table('s_groups')
            ->whereNull('DELETED_AT')
            ->orderBy('id', 'asc')
            ->get()
            ->toArray();
    }

    public function getRole(int $id)
    {
        return DB::table('s_groups')->where('id', $id)->first();
    }

    public function allMenuAccess(): array
    {
        $headers = DB::table('s_menu')
            ->where('TAMPIL', 1)
            ->whereNull('PARENT_ID')
            ->whereNull('HEADER_ID')
            ->orderBy('URUT', 'asc')
            ->get();

        $menus = DB::table('s_menu')
            ->where('TAMPIL', 1)
            ->whereNotNull('HEADER_ID')
            ->whereNull('PARENT_ID')
            ->orderBy('URUT', 'asc')
            ->get();

        $subMenus = DB::table('s_menu')
            ->where('TAMPIL', 1)
            ->whereNotNull('HEADER_ID')
            ->whereNotNull('PARENT_ID')
            ->orderBy('URUT', 'asc')
            ->get();

        $permissions = DB::table('s_permission')
            ->whereNotNull('ID_MENU')
            ->orderBy('ID_MENU', 'asc')
            ->orderBy('URUT', 'asc')
            ->get();

        $allMenuAccess = [];
        foreach ($headers as $head) {
            $dataMenu = [];
            foreach ($menus as $menu) {
                if ($head->ID_MENU === $menu->HEADER_ID) {
                    $dataSubMenu = [];
                    foreach ($subMenus as $subMenu) {
                        if ($menu->ID_MENU === $subMenu->PARENT_ID) {
                            $subMenuPermissions = [];
                            foreach ($permissions as $ps) {
                                if ($ps->ID_MENU == $subMenu->ID_MENU) {
                                    $subMenuPermissions[] = [
                                        'p_id' => $ps->ID_PERMISSION,
                                        'p_nama' => $ps->NAMA,
                                        'p_slug' => $ps->SLUG,
                                    ];
                                }
                            }
                            $dataSubMenu[] = [
                                'sub_menu_id' => $subMenu->ID_MENU,
                                'sub_menu_nama' => $subMenu->NAMA,
                                'sub_menu_icon' => $subMenu->ICON,
                                'sub_menu_link' => $subMenu->LINK ?? '',
                                'sub_menu_slug' => $subMenu->SLUG ?? '',
                                'sub_menu_permissions' => $subMenuPermissions,
                            ];
                        }
                    }

                    $menuPermissions = [];
                    foreach ($permissions as $psa) {
                        if ($psa->ID_MENU == $menu->ID_MENU) {
                            $menuPermissions[] = [
                                'p_id' => $psa->ID_PERMISSION,
                                'p_nama' => $psa->NAMA,
                                'p_slug' => $psa->SLUG,
                            ];
                        }
                    }

                    $dataMenu[] = [
                        'menu_id' => $menu->ID_MENU,
                        'menu_nama' => $menu->NAMA,
                        'menu_icon' => $menu->ICON,
                        'menu_link' => $menu->LINK ?? '',
                        'menu_slug' => $menu->SLUG ?? '',
                        'sub_menus' => $dataSubMenu,
                        'menu_permissions' => $menuPermissions,
                    ];
                }
            }

            $allMenuAccess[] = [
                'id' => $head->ID_MENU,
                'nama' => $head->NAMA,
                'icon' => $head->ICON,
                'link' => $head->LINK ?? '',
                'slug' => $head->SLUG ?? '',
                'menus' => $dataMenu,
            ];
        }

        return $allMenuAccess;
    }

    public function getRoleActivePermissions(int $roleId): array
    {
        return DB::table('s_group_permissions')
            ->where('ID_GROUP', $roleId)
            ->pluck('ID_PERMISSION')
            ->toArray();
    }

    public function updateRolePermissionsAndMenus(int $roleId, array $permissionIds): void
    {
        // 1. Delete existing group permissions
        DB::table('s_group_permissions')->where('ID_GROUP', $roleId)->delete();

        // 2. Insert new permissions
        $permInserts = [];
        $menuIds = [];

        if (!empty($permissionIds)) {
            // Find which menus these permissions belong to
            $perms = DB::table('s_permission')
                ->whereIn('ID_PERMISSION', $permissionIds)
                ->get();

            foreach ($perms as $p) {
                $permInserts[] = [
                    'ID_GROUP' => $roleId,
                    'ID_PERMISSION' => $p->ID_PERMISSION,
                ];
                if ($p->ID_MENU) {
                    $menuIds[] = $p->ID_MENU;
                }
            }

            if (!empty($permInserts)) {
                DB::table('s_group_permissions')->insert($permInserts);
            }
        }

        // 3. For any menu with active permissions, ensure parent and header menus are also in s_group_menu
        $menuIds = array_unique($menuIds);
        if (!empty($menuIds)) {
            $menus = DB::table('s_menu')->whereIn('ID_MENU', $menuIds)->get();
            $allRequiredMenuIds = $menuIds;
            foreach ($menus as $m) {
                if ($m->PARENT_ID) $allRequiredMenuIds[] = $m->PARENT_ID;
                if ($m->HEADER_ID) $allRequiredMenuIds[] = $m->HEADER_ID;
            }
            $allRequiredMenuIds = array_unique($allRequiredMenuIds);

            DB::table('s_group_menu')->where('ID_GROUP', $roleId)->delete();
            $menuInserts = [];
            foreach ($allRequiredMenuIds as $mId) {
                $menuInserts[] = [
                    'ID_GROUP' => $roleId,
                    'ID_MENU' => $mId,
                ];
            }
            if (!empty($menuInserts)) {
                DB::table('s_group_menu')->insert($menuInserts);
            }
        } else {
            DB::table('s_group_menu')->where('ID_GROUP', $roleId)->delete();
        }
    }

    // ==========================================
    // PENGGUNA
    // ==========================================

    public function getPenggunaTable(bool $showDeleted = false): array
    {
        $query = DB::table('s_users as u')
            ->leftJoin('s_users_groups as ug', 'u.id', '=', 'ug.user_id')
            ->leftJoin('s_groups as g', 'ug.group_id', '=', 'g.id')
            ->select(
                'u.id',
                'u.username',
                'u.email',
                'u.active',
                'u.avatar',
                'u.created_on',
                'u.last_login',
                'u.DELETED_AT',
                'g.name as role_name',
                'g.id as role_id'
            );

        if ($showDeleted) {
            $query->whereNotNull('u.DELETED_AT');
        } else {
            $query->whereNull('u.DELETED_AT');
        }

        return $query->orderBy('u.id', 'desc')->get()->toArray();
    }

    public function getPengguna(int $id)
    {
        return DB::table('s_users as u')
            ->leftJoin('s_users_groups as ug', 'u.id', '=', 'ug.user_id')
            ->leftJoin('s_groups as g', 'ug.group_id', '=', 'g.id')
            ->where('u.id', $id)
            ->select('u.*', 'g.id as role_id', 'g.name as role_name')
            ->first();
    }

    // ==========================================
    // MENU & AKSES
    // ==========================================

    public function getAksesTable(): array
    {
        return DB::table('s_menu as a')
            ->leftJoin('s_menu as b', 'a.HEADER_ID', '=', 'b.ID_MENU')
            ->leftJoin('s_menu as c', 'a.PARENT_ID', '=', 'c.ID_MENU')
            ->select(
                'a.ID_MENU',
                'a.NAMA',
                'a.ICON',
                'a.LINK',
                'a.SLUG',
                'a.TAMPIL',
                'a.URUT',
                'b.NAMA as HEADER_NAMA',
                'c.NAMA as PARENT_NAMA'
            )
            ->orderBy('a.URUT', 'asc')
            ->orderBy('a.ID_MENU', 'asc')
            ->get()
            ->toArray();
    }

    public function getAkses(int $id)
    {
        return DB::table('s_menu as a')
            ->leftJoin('s_menu as b', 'a.HEADER_ID', '=', 'b.ID_MENU')
            ->leftJoin('s_menu as c', 'a.PARENT_ID', '=', 'c.ID_MENU')
            ->where('a.ID_MENU', $id)
            ->select(
                'a.*',
                'b.NAMA as HEADER_NAMA',
                'c.NAMA as PARENT_NAMA'
            )
            ->first();
    }

    // ==========================================
    // CRON JOBS
    // ==========================================

    public function getCronJobsTable(): array
    {
        return DB::table('s_cron_jobs')
            ->orderBy('JOB_NAME', 'asc')
            ->get()
            ->toArray();
    }

    public function getCronJob(int $id)
    {
        return DB::table('s_cron_jobs')->where('ID_CRON_JOB', $id)->first();
    }
}
