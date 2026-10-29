<?php

namespace Tests\Feature\Accounting;

use App\Models\Invoice;
use App\Models\UpahTkbm;
use App\Models\User;
use App\Models\Workorder;
use Tests\TestCase;

class UpahTkbmTest extends TestCase
{
    protected function getAdminUser(): User
    {
        return User::find(2) ?? User::first();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/accounting/upah');
        $response->assertRedirect('/login');
    }

    public function test_upah_index_screen_can_be_rendered(): void
    {
        $user = $this->getAdminUser();

        $response = $this->actingAs($user)->get('/accounting/upah');

        $response->assertStatus(200);
        $response->assertSee('Daftar Perhitungan');
        $response->assertSee('crudTable');
    }

    public function test_upah_table_returns_json_array(): void
    {
        $user = $this->getAdminUser();

        $response = $this->actingAs($user)->postJson('/accounting/upah/table');

        $response->assertStatus(200);
        $this->assertIsArray($response->json());
    }

    public function test_upah_crud_and_3_print_cycle(): void
    {
        $user = $this->getAdminUser();

        $wo = Workorder::first();
        if (!$wo) {
            $this->markTestSkipped('Tidak ada Work Order untuk pengetesan Upah TKBM');
        }

        // 1. Create page
        $createRes = $this->actingAs($user)->get('/accounting/upah/create/' . $wo->ID_WORK_ORDER);
        $createRes->assertStatus(200);
        $createRes->assertSee('Identitas Pekerjaan');

        // 2. Test AJAX Pull WO Data
        $pullRes = $this->actingAs($user)->postJson('/accounting/upah/pull_wo_data', [
            'wo_id' => $wo->ID_WORK_ORDER,
        ]);
        $pullRes->assertStatus(200);
        $pullRes->assertJson(['status' => true]);

        // 3. Store new Upah TKBM
        $storeData = [
            'id_work_order' => $wo->ID_WORK_ORDER,
            'no_dokumen' => 'TKBM-UPH-TEST-0001',
            'no_kwitansi' => '9999/KTKBM.Tgl/' . date('Y'),
            'tgl_kegiatan_start' => '2026-09-28',
            'tgl_kegiatan_end' => '2026-10-02',
            'komoditas' => 'B. BATU BARA',
            'rekening_bank' => '(BCA) 6100091044 / (MANDIRI) 1780068844444 AN. ERNAWATI/THOHIR',
            'koordinator_nama' => 'H. M. THOHIR, SE',
            'status' => 'published',
            'tgl_kerja' => ['2026-09-28', '2026-09-29'],
            'jam_kerja' => ['19 - 07', '07 - 19'],
            'jumlah_gang' => [2, 2],
            'jumlah_harian' => [10, 12],
            'jumlah_truck' => [50, 64],
            'volume_borongan' => [1793.000, 2295.000],
            'upah_nominal' => [6000000, 8000000],
            'adm_nominal' => [500000, 500000],
            'toeslag_nominal' => [400000, 0],
            'lembur_nominal' => [0, 411508],
            'keterangan' => ['Shift Malam', 'Shift Siang'],
            'alokasi_buruh' => 14811508,
            'alokasi_koperasi' => 1000000,
        ];

        // 3A. Test Allocation Imbalance Validation
        $imbalancedData = $storeData;
        $imbalancedData['alokasi_buruh'] = 10000000;
        $imbalancedData['alokasi_koperasi'] = 2000000; // Total 12jt != 15.8jt
        $imbalancedRes = $this->actingAs($user)->postJson('/accounting/upah/store', $imbalancedData);
        $imbalancedRes->assertStatus(200);
        $imbalancedRes->assertJson(['status' => false]);
        $this->assertStringContainsString('harus sama dengan Grand Total', $imbalancedRes->json('msg'));

        $storeRes = $this->actingAs($user)->postJson('/accounting/upah/store', $storeData);
        $storeRes->assertStatus(200);
        $storeRes->assertJson(['status' => true]);

        $upahId = $storeRes->json('id');
        $this->assertNotNull($upahId);

        $upah = UpahTkbm::find($upahId);
        $this->assertNotNull($upah);
        $this->assertEquals(15811508, $upah->GRAND_TOTAL);
        $this->assertEquals(114, $upah->TOTAL_TRUCK);
        $this->assertEquals(4088.000, $upah->TOTAL_VOLUME);
        $this->assertStringContainsString('RUPIAH', $upah->TERBILANG);

        // Verify JUMLAH_HARIAN on details
        $details = $upah->details;
        $this->assertCount(2, $details);
        $this->assertEquals(10, $details[0]->JUMLAH_HARIAN);
        $this->assertEquals(12, $details[1]->JUMLAH_HARIAN);

        // 4. Detail page
        $detailRes = $this->actingAs($user)->get('/accounting/upah/detail/' . $upahId);
        $detailRes->assertStatus(200);
        $detailRes->assertSee($upah->NO_KWITANSI);
        $detailRes->assertSee('B. BATU BARA');

        // 5. Edit page
        $editRes = $this->actingAs($user)->get('/accounting/upah/edit/' . $upahId);
        $editRes->assertStatus(200);
        $editRes->assertSee('Edit Perhitungan');

        // 6. Test 3 Print Pages
        // 6A. Cetak Lembar Perhitungan Upah (Foto 1)
        $printPerhitunganRes = $this->actingAs($user)->get('/accounting/upah/print_perhitungan/' . $upahId);
        $printPerhitunganRes->assertStatus(200);
        $printPerhitunganRes->assertSee('PERHITUNGAN UPAH TKBM');
        $printPerhitunganRes->assertSee('K.TKBM PELABUHAN GRESIK');

        // 6B. Cetak Rekap Tally Truk (Foto 2)
        $printTallyRes = $this->actingAs($user)->get('/accounting/upah/print_tally/' . $upahId);
        $printTallyRes->assertStatus(200);
        $printTallyRes->assertSee('TRUCK');
        $printTallyRes->assertSee('VOLUME');

        // 6C. Cetak Kwitansi Resmi K.TKBM (Foto 3)
        $printKwitansiRes = $this->actingAs($user)->get('/accounting/upah/print_kwitansi/' . $upahId);
        $printKwitansiRes->assertStatus(200);
        $printKwitansiRes->assertSee('KOPERASI TENAGA KERJA BONGKAR MUAT');
        $printKwitansiRes->assertSee('Bagian Upah TKBM');
        $printKwitansiRes->assertSee('Bagian Koperasi TKBM');

        // 7. Test Bridge to Commercial Invoice
        $bridgeRes = $this->actingAs($user)->postJson('/accounting/upah/bridge_invoice', [
            'id_upah_tkbm' => $upahId,
        ]);
        $bridgeRes->assertStatus(200);
        $bridgeRes->assertJson(['status' => true]);
        $invId = $bridgeRes->json('invoice_id');
        $this->assertNotNull($invId);
        $inv = Invoice::find($invId);
        $this->assertNotNull($inv);
        $this->assertEquals(15811508, $inv->TOTAL);

        // 8. Test Update Payment (Confirm Lunas)
        $payRes = $this->actingAs($user)->postJson('/accounting/upah/update_payment', [
            'id_upah_tkbm' => $upahId,
            'tanggal_bayar' => date('Y-m-d'),
        ]);
        $payRes->assertStatus(200);
        $payRes->assertJson(['status' => true]);

        $upah->refresh();
        $this->assertEquals('paid', $upah->STATUS);

        // 8A. Test Guard: Cannot delete or cancel paid document
        $guardDelRes = $this->actingAs($user)->postJson('/accounting/upah/delete', ['id' => $upahId]);
        $guardDelRes->assertStatus(200);
        $guardDelRes->assertJson(['status' => false]);
        $this->assertStringContainsString('LUNAS tidak dapat dihapus', $guardDelRes->json('msg'));

        $guardCancelRes = $this->actingAs($user)->postJson('/accounting/upah/cancel', ['id_upah_tkbm' => $upahId]);
        $guardCancelRes->assertStatus(200);
        $guardCancelRes->assertJson(['status' => false]);
        $this->assertStringContainsString('LUNAS tidak dapat dibatalkan', $guardCancelRes->json('msg'));

        // 8B. Test cancel on published document
        $upah->update(['STATUS' => 'published']);
        $cancelRes = $this->actingAs($user)->postJson('/accounting/upah/cancel', ['id_upah_tkbm' => $upahId]);
        $cancelRes->assertStatus(200);
        $cancelRes->assertJson(['status' => true]);
        $upah->refresh();
        $this->assertEquals('cancelled', $upah->STATUS);

        // 9. Test Delete and Restore
        $deleteRes = $this->actingAs($user)->postJson('/accounting/upah/delete', ['id' => $upahId]);
        $deleteRes->assertStatus(200);
        $this->assertTrue(UpahTkbm::withTrashed()->find($upahId)->trashed());

        $restoreRes = $this->actingAs($user)->postJson('/accounting/upah/restore', ['id' => $upahId]);
        $restoreRes->assertStatus(200);
        $this->assertFalse(UpahTkbm::find($upahId)->trashed());

        // Cleanup test data
        $upah->forceDelete();
        $inv->forceDelete();
    }
}
