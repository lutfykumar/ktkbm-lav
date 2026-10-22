<?php

namespace Tests\Feature\Master;

use App\Models\Aktifitas;
use App\Models\User;
use Tests\TestCase;

class AktifitasTest extends TestCase
{
    public function test_authenticated_admin_can_view_aktifitas_index(): void
    {
        $admin = User::find(2); // Administrator (admin group)
        $this->assertNotNull($admin);

        $response = $this->actingAs($admin)->get('/master/aktifitas');

        $response->assertStatus(200);
        $response->assertSee('Aktifitas');
    }

    public function test_authenticated_admin_can_fetch_table_json(): void
    {
        $admin = User::find(2);

        $response = $this->actingAs($admin)->getJson('/master/aktifitas/table');

        $response->assertStatus(200);
        $this->assertIsArray($response->json());
    }

    public function test_authenticated_admin_can_store_aktifitas(): void
    {
        $admin = User::find(2);

        $testName = 'Test Aktifitas ' . time();
        $response = $this->actingAs($admin)->postJson('/master/aktifitas/store', [
            'name' => $testName,
            'tipe' => 'bongkar',
            'status' => 1,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => true,
        ]);

        $created = Aktifitas::where('NAMA', $testName)->first();
        $this->assertNotNull($created);

        // Clean up test data
        $created->forceDelete();
    }
}
