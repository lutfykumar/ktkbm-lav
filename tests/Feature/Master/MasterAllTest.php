<?php

namespace Tests\Feature\Master;

use App\Models\User;
use Tests\TestCase;

class MasterAllTest extends TestCase
{
    protected array $modules = [
        'aktifitas',
        'alat',
        'catatan',
        'client',
        'dermaga',
        'kapal',
        'krk',
        'pelabuhan',
        'satuan',
    ];

    public function test_admin_can_access_all_master_index_pages(): void
    {
        $admin = User::find(2);
        $this->assertNotNull($admin);

        foreach ($this->modules as $mod) {
            $response = $this->actingAs($admin)->get("/master/{$mod}");
            $response->assertStatus(200);
        }
    }

    public function test_admin_can_fetch_all_master_tables_json(): void
    {
        $admin = User::find(2);
        $this->assertNotNull($admin);

        foreach ($this->modules as $mod) {
            $response = $this->actingAs($admin)->getJson("/master/{$mod}/table");
            $response->assertStatus(200);
            $this->assertIsArray($response->json());
        }
    }

    public function test_all_master_index_pages_contain_restore_handler(): void
    {
        $admin = User::find(2);
        $this->assertNotNull($admin);

        foreach ($this->modules as $mod) {
            $response = $this->actingAs($admin)->get("/master/{$mod}");
            $response->assertStatus(200);
            $response->assertSee('.btn-restore');
        }
    }
}
