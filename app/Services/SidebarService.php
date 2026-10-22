<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class SidebarService
{
    /**
     * Get sidebar structure for a given user
     */
    public function getForUser(?User $user): array
    {
        if (!$user) {
            return [];
        }

        $roleId = $user->group_id;
        if (!$roleId) {
            return [];
        }

        $cacheKey = 'sidebar_role_' . $roleId;

        return Cache::remember($cacheKey, 3600, function () use ($roleId) {
            // Get header menus
            $headers = DB::table('s_group_menu as aa')
                ->join('s_menu as cc', 'cc.ID_MENU', '=', 'aa.ID_MENU')
                ->where('aa.ID_GROUP', $roleId)
                ->where('cc.TAMPIL', 1)
                ->whereNull('cc.PARENT_ID')
                ->whereNull('cc.HEADER_ID')
                ->select('cc.*')
                ->groupBy('cc.ID_MENU')
                ->orderBy('cc.URUT', 'ASC')
                ->get();

            // Get main menus (under a header)
            $menus = DB::table('s_group_menu as aa')
                ->join('s_menu as cc', 'cc.ID_MENU', '=', 'aa.ID_MENU')
                ->where('aa.ID_GROUP', $roleId)
                ->where('cc.TAMPIL', 1)
                ->whereNotNull('cc.HEADER_ID')
                ->whereNull('cc.PARENT_ID')
                ->select('cc.*')
                ->groupBy('cc.ID_MENU')
                ->orderBy('cc.URUT', 'ASC')
                ->get();

            // Get sub menus
            $subMenus = DB::table('s_group_menu as aa')
                ->join('s_menu as cc', 'cc.ID_MENU', '=', 'aa.ID_MENU')
                ->where('aa.ID_GROUP', $roleId)
                ->where('cc.TAMPIL', 1)
                ->whereNotNull('cc.HEADER_ID')
                ->whereNotNull('cc.PARENT_ID')
                ->select('cc.*')
                ->groupBy('cc.ID_MENU')
                ->orderBy('cc.URUT', 'ASC')
                ->get();

            // Get permissions mapped to menus
            $permissions = DB::table('s_permission')
                ->whereNotNull('ID_MENU')
                ->orderBy('ID_MENU')
                ->orderBy('URUT', 'ASC')
                ->get();

            $sidebar = [];

            foreach ($headers as $head) {
                $dataMenus = [];

                foreach ($menus as $menu) {
                    if (empty($menu->NAMA)) {
                        continue;
                    }

                    $dataSubMenus = [];
                    foreach ($subMenus as $subMenu) {
                        if (empty($subMenu->NAMA) || $menu->ID_MENU !== $subMenu->PARENT_ID) {
                            continue;
                        }

                        $subMenuPerms = [];
                        foreach ($permissions as $ps) {
                            if ($ps->ID_MENU == $subMenu->ID_MENU) {
                                $subMenuPerms[] = [
                                    'p_id' => $ps->ID_PERMISSION,
                                    'p_nama' => $ps->NAMA,
                                    'p_slug' => $ps->SLUG,
                                ];
                            }
                        }

                        $dataSubMenus[] = [
                            'sub_menu_id' => $subMenu->ID_MENU,
                            'sub_menu_nama' => $subMenu->NAMA,
                            'sub_menu_icon' => $subMenu->ICON,
                            'sub_menu_link' => $subMenu->LINK ?: '',
                            'sub_menu_slug' => $subMenu->SLUG ?: '',
                            'sub_menu_permissions' => $subMenuPerms,
                        ];
                    }

                    if ($head->ID_MENU === $menu->HEADER_ID) {
                        $menuPerms = [];
                        foreach ($permissions as $psa) {
                            if ($psa->ID_MENU == $menu->ID_MENU) {
                                $menuPerms[] = [
                                    'p_id' => $psa->ID_PERMISSION,
                                    'p_nama' => $psa->NAMA,
                                    'p_slug' => $psa->SLUG,
                                ];
                            }
                        }

                        $dataMenus[] = [
                            'menu_id' => $menu->ID_MENU,
                            'menu_nama' => $menu->NAMA,
                            'menu_icon' => $menu->ICON,
                            'menu_link' => $menu->LINK ?: '',
                            'menu_slug' => $menu->SLUG ?: '',
                            'sub_menus' => $dataSubMenus,
                            'menu_permissions' => $menuPerms,
                        ];
                    }
                }

                $sidebar[] = [
                    'id' => $head->ID_MENU,
                    'nama' => $head->NAMA,
                    'icon' => $head->ICON,
                    'link' => $head->LINK ?: '',
                    'slug' => $head->SLUG ?: '',
                    'menus' => $dataMenus,
                ];
            }

            return $sidebar;
        });
    }

    /**
     * Get entire sidebar structure for menu management
     */
    public function getSidebarMenu(): array
    {
        $headers = DB::table('s_menu as cc')
            ->where('cc.TAMPIL', 1)
            ->whereNull('cc.PARENT_ID')
            ->whereNull('cc.HEADER_ID')
            ->orderBy('cc.URUT', 'ASC')
            ->get();

        $menus = DB::table('s_menu as cc')
            ->where('cc.TAMPIL', 1)
            ->whereNotNull('cc.HEADER_ID')
            ->whereNull('cc.PARENT_ID')
            ->orderBy('cc.URUT', 'ASC')
            ->get();

        $subMenus = DB::table('s_menu as cc')
            ->where('cc.TAMPIL', 1)
            ->whereNotNull('cc.HEADER_ID')
            ->whereNotNull('cc.PARENT_ID')
            ->orderBy('cc.URUT', 'ASC')
            ->get();

        $sidebar = [];
        foreach ($headers as $head) {
            $dataMenus = [];
            foreach ($menus as $menu) {
                if ($head->ID_MENU === $menu->HEADER_ID) {
                    $dataSubMenus = [];
                    foreach ($subMenus as $subMenu) {
                        if ($menu->ID_MENU === $subMenu->PARENT_ID) {
                            $dataSubMenus[] = [
                                'sub_menu_id' => $subMenu->ID_MENU,
                                'sub_menu_nama' => $subMenu->NAMA,
                                'sub_menu_icon' => $subMenu->ICON,
                                'sub_menu_link' => $subMenu->LINK ?: '',
                                'sub_menu_slug' => $subMenu->SLUG ?: '',
                            ];
                        }
                    }
                    $dataMenus[] = [
                        'menu_id' => $menu->ID_MENU,
                        'menu_nama' => $menu->NAMA,
                        'menu_icon' => $menu->ICON,
                        'menu_link' => $menu->LINK ?: '',
                        'menu_slug' => $menu->SLUG ?: '',
                        'sub_menus' => $dataSubMenus,
                    ];
                }
            }
            $sidebar[] = [
                'id' => $head->ID_MENU,
                'nama' => $head->NAMA,
                'icon' => $head->ICON,
                'link' => $head->LINK ?: '',
                'slug' => $head->SLUG ?: '',
                'menus' => $dataMenus,
            ];
        }

        return $sidebar;
    }

    /**
     * Clear sidebar cache for a role or all roles
     */
    public function clearCache(?int $roleId = null): void
    {
        if ($roleId) {
            Cache::forget('sidebar_role_' . $roleId);
        } else {
            for ($i = 1; $i <= 20; $i++) {
                Cache::forget('sidebar_role_' . $i);
            }
        }
    }
}

