<?php

namespace Tests\Feature\Report;

use App\Models\User;
use App\Models\Pelabuhan;
use Tests\TestCase;

class WorkorderReportTest extends TestCase
{
    protected function getAdminUser(): User
    {
        return User::find(2) ?? User::first();
    }

    public function test_report_workorder_index_can_be_rendered(): void
    {
        $user = $this->getAdminUser();

        $response = $this->actingAs($user)->get('/report/workorder');

        $response->assertStatus(200);
        $response->assertSee('Report Work Order');
        $response->assertSee('Rekap Kegiatan Kapal');
    }

    public function test_report_workorder_get_report_data(): void
    {
        $user = $this->getAdminUser();

        $response = $this->actingAs($user)->postJson('/report/workorder/get_report_data', [
            'tgl_start' => date('Y-01-01'),
            'tgl_end' => date('Y-m-d'),
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'documents',
            'total_wo',
            'periode'
        ]);
    }

    public function test_report_workorder_get_kegiatan_kapal(): void
    {
        $user = $this->getAdminUser();
        $pelabuhan = Pelabuhan::first();
        $this->assertNotNull($pelabuhan);

        $response = $this->actingAs($user)->postJson('/report/workorder/get_kegiatan_kapal', [
            'pelabuhan' => $pelabuhan->ID_PELABUHAN,
            'bulan_tahun' => date('Y-m'),
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'header_title',
            'header_periode',
            'rows',
            'total_kapal',
            'summary_pbm',
            'summary_komoditas'
        ]);
    }
}
