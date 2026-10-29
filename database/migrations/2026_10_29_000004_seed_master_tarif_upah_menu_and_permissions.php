<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $now = date('Y-m-d H:i:s');

        // Check if menu exists
        $menuId = DB::table('s_menu')->where('SLUG', 'master-tarif_upah')->value('ID_MENU');

        if (!$menuId) {
            $menuId = DB::table('s_menu')->insertGetId([
                'CREATED_BY' => 1,
                'HEADER_ID' => 3, // Master Data
                'PARENT_ID' => null,
                'NAMA' => 'Tarif Upah TKBM',
                'SLUG' => 'master-tarif_upah',
                'ICON' => 'fa-money-bill-wave',
                'LINK' => 'master/tarif_upah',
                'TAMPIL' => 1,
                'URUT' => 20,
                'CREATED_AT' => $now,
                'UPDATED_AT' => $now,
            ]);
        }

        // Grant menu to groups 1 (Superadmin), 2 (Admin), 3 (Keuangan)
        foreach ([1, 2, 3] as $groupId) {
            $hasGroupMenu = DB::table('s_group_menu')
                ->where('ID_GROUP', $groupId)
                ->where('ID_MENU', $menuId)
                ->exists();

            if (!$hasGroupMenu) {
                DB::table('s_group_menu')->insert([
                    'CREATED_BY' => 1,
                    'ID_GROUP' => $groupId,
                    'ID_MENU' => $menuId,
                    'CREATED_AT' => $now,
                ]);
            }
        }

        // Permissions
        $permissions = [
            ['NAMA' => 'Read Tarif Upah', 'SLUG' => 'read-master-tarif_upah', 'URUT' => 1],
            ['NAMA' => 'Create Tarif Upah', 'SLUG' => 'create-master-tarif_upah', 'URUT' => 2],
            ['NAMA' => 'Update Tarif Upah', 'SLUG' => 'update-master-tarif_upah', 'URUT' => 3],
            ['NAMA' => 'Delete Tarif Upah', 'SLUG' => 'delete-master-tarif_upah', 'URUT' => 4],
        ];

        foreach ($permissions as $p) {
            $permId = DB::table('s_permission')->where('SLUG', $p['SLUG'])->value('ID_PERMISSION');
            if (!$permId) {
                $permId = DB::table('s_permission')->insertGetId([
                    'ID_MENU' => $menuId,
                    'NAMA' => $p['NAMA'],
                    'SLUG' => $p['SLUG'],
                    'URUT' => $p['URUT'],
                ]);
            }

            // Assign permission to groups 1, 2, 3
            foreach ([1, 2, 3] as $groupId) {
                $hasGroupPerm = DB::table('s_group_permissions')
                    ->where('ID_GROUP', $groupId)
                    ->where('ID_PERMISSION', $permId)
                    ->exists();

                if (!$hasGroupPerm) {
                    DB::table('s_group_permissions')->insert([
                        'ID_GROUP' => $groupId,
                        'ID_PERMISSION' => $permId,
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $menuId = DB::table('s_menu')->where('SLUG', 'master-tarif_upah')->value('ID_MENU');
        if ($menuId) {
            $permIds = DB::table('s_permission')->where('ID_MENU', $menuId)->pluck('ID_PERMISSION')->toArray();
            if (!empty($permIds)) {
                DB::table('s_group_permissions')->whereIn('ID_PERMISSION', $permIds)->delete();
                DB::table('s_permission')->where('ID_MENU', $menuId)->delete();
            }
            DB::table('s_group_menu')->where('ID_MENU', $menuId)->delete();
            DB::table('s_menu')->where('ID_MENU', $menuId)->delete();
        }
    }
};

