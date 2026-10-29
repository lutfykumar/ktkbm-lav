<?php

namespace Tests\Feature\Master;

use App\Models\TarifUpah;
use App\Models\User;
use Tests\TestCase;

class TarifUpahTest extends TestCase
{
    protected function getAdminUser(): User
    {
        return User::find(2) ?? User::first();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/master/tarif_upah');
        $response->assertRedirect('/login');
    }

    public function test_tarif_upah_index_screen_can_be_rendered(): void
    {
        $user = $this->getAdminUser();

        $response = $this->actingAs($user)->get('/master/tarif_upah');

        $response->assertStatus(200);
        $response->assertSee('Tarif Upah TKBM');
        $response->assertSee('crudTable');
    }

    public function test_tarif_upah_table_returns_json_array(): void
    {
        $user = $this->getAdminUser();

        $response = $this->actingAs($user)->getJson('/master/tarif_upah/table');

        $response->assertStatus(200);
        $this->assertIsArray($response->json());
    }

    public function test_tarif_upah_crud_cycle(): void
    {
        $user = $this->getAdminUser();

        // 1. Store
        $storeRes = $this->actingAs($user)->postJson('/master/tarif_upah/store', [
            'nama_tarif' => 'Tarif Bongkar Semen Bag Test',
            'jenis_tarif' => 'borongan',
            'nilai_tarif' => '25000',
            'persen_kas_koperasi' => '10',
            'keterangan' => 'Uji Coba Otomasi Test',
            'is_active' => '1',
        ]);

        $storeRes->assertStatus(200);
        $storeRes->assertJson(['status' => true]);

        $tarif = TarifUpah::where('NAMA_TARIF', 'Tarif Bongkar Semen Bag Test')->first();
        $this->assertNotNull($tarif);
        $this->assertEquals(25000, $tarif->NILAI_TARIF);
        $this->assertEquals(10, $tarif->PERSEN_KAS_KOPERASI);
        $this->assertEquals('borongan', $tarif->JENIS_TARIF);

        // 2. Edit View
        $editRes = $this->actingAs($user)->post('/master/tarif_upah/edit', [
            'id' => $tarif->ID_TARIF,
        ]);
        $editRes->assertStatus(200);
        $editRes->assertSee('Tarif Bongkar Semen Bag Test');

        // 3. Update
        $updateRes = $this->actingAs($user)->postJson('/master/tarif_upah/update', [
            'model_id' => $tarif->ID_TARIF,
            'u_nama_tarif' => 'Tarif Bongkar Semen Bag Updated',
            'u_jenis_tarif' => 'borongan',
            'u_nilai_tarif' => '27500',
            'u_persen_kas_koperasi' => '12.5',
            'u_keterangan' => 'Revisi Hasil Kesepakatan',
            'u_is_active' => '1',
        ]);
        $updateRes->assertStatus(200);
        $updateRes->assertJson(['status' => true]);

        $tarif->refresh();
        $this->assertEquals('Tarif Bongkar Semen Bag Updated', $tarif->NAMA_TARIF);
        $this->assertEquals(27500, $tarif->NILAI_TARIF);
        $this->assertEquals(12.5, $tarif->PERSEN_KAS_KOPERASI);

        // 4. Delete
        $deleteRes = $this->actingAs($user)->postJson('/master/tarif_upah/delete', [
            'id' => $tarif->ID_TARIF,
        ]);
        $deleteRes->assertStatus(200);
        $deleteRes->assertJson(['status' => true]);
        $this->assertTrue(TarifUpah::withTrashed()->find($tarif->ID_TARIF)->trashed());

        // 5. Restore
        $restoreRes = $this->actingAs($user)->postJson('/master/tarif_upah/restore', [
            'id' => $tarif->ID_TARIF,
        ]);
        $restoreRes->assertStatus(200);
        $restoreRes->assertJson(['status' => true]);
        $this->assertFalse(TarifUpah::find($tarif->ID_TARIF)->trashed());

        // Cleanup
        $tarif->forceDelete();
    }
}

