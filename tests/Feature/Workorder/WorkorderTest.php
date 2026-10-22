<?php

namespace Tests\Feature\Workorder;

use App\Models\User;
use App\Models\Workorder;
use App\Models\Client;
use App\Models\Kapal;
use App\Models\Dermaga;
use App\Models\Aktifitas;
use Tests\TestCase;

class WorkorderTest extends TestCase
{
    protected function getAdminUser(): User
    {
        return User::find(2) ?? User::first();
    }

    public function test_workorder_index_screen_can_be_rendered(): void
    {
        $user = $this->getAdminUser();

        $response = $this->actingAs($user)->get('/workorder');

        $response->assertStatus(200);
        $response->assertSee('Work Order');
        $response->assertSee('crudTable');
    }

    public function test_workorder_get_no_work_order(): void
    {
        $user = $this->getAdminUser();

        $response = $this->actingAs($user)->postJson('/workorder/get_no_work_order', [
            'tgl' => date('Y-m-d')
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'no_work_order',
            'data' => ['no_work_order']
        ]);
        $this->assertStringStartsWith('TKBM-WO-', $response->json('no_work_order'));
    }

    public function test_workorder_table_returns_json_array(): void
    {
        $user = $this->getAdminUser();

        $response = $this->actingAs($user)->postJson('/workorder/table');

        $response->assertStatus(200);
        $this->assertIsArray($response->json());
    }

    public function test_workorder_crud_cycle(): void
    {
        $user = $this->getAdminUser();
        $client = Client::first();
        $kapal = Kapal::first();
        $dermaga = Dermaga::first();
        $aktifitas = Aktifitas::first();

        $this->assertNotNull($client, 'Client test fixture not found');
        $this->assertNotNull($kapal, 'Kapal test fixture not found');
        $this->assertNotNull($dermaga, 'Dermaga test fixture not found');

        $testNoWo = 'TKBM-TEST-WO-' . uniqid();

        // 1. Store
        $storeResponse = $this->actingAs($user)->postJson('/workorder/store', [
            'tgl_work_order' => date('Y-m-d'),
            'no_work_order' => $testNoWo,
            'is_manual_no' => 1,
            'client_id' => $client->ID_CLIENT,
            'kapal_id' => $kapal->ID_KAPAL,
            'dermaga_id' => $dermaga->ID_DERMAGA,
            'aktifitas_id' => $aktifitas ? $aktifitas->ID_AKTIFITAS : null,
            'status' => 'open'
        ]);

        $storeResponse->assertStatus(200);
        $storeResponse->assertJson(['status' => true]);

        $created = Workorder::where('NO_WORK_ORDER', $testNoWo)->first();
        $this->assertNotNull($created);
        $woId = $created->ID_WORK_ORDER;

        try {
            // 2. Detail modal HTML
            $detailResponse = $this->actingAs($user)->post('/workorder/detail', [
                'id' => $woId
            ]);
            $detailResponse->assertStatus(200);
            $detailResponse->assertSee($testNoWo);

            // 3. Edit modal HTML
            $editResponse = $this->actingAs($user)->post('/workorder/edit', [
                'id' => $woId
            ]);
            $editResponse->assertStatus(200);
            $editResponse->assertSee($testNoWo);

            // 4. Update
            $updateResponse = $this->actingAs($user)->postJson('/workorder/update', [
                'model_id' => $woId,
                'u_no_work_order' => $testNoWo,
                'u_client_id' => $client->ID_CLIENT,
                'u_kapal_id' => $kapal->ID_KAPAL,
                'u_dermaga_id' => $dermaga->ID_DERMAGA,
                'u_status' => 'progress'
            ]);
            $updateResponse->assertStatus(200);
            $updateResponse->assertJson(['status' => true]);

            $this->assertEquals('progress', Workorder::find($woId)->STATUS);

            // 5. Update Status
            $statusResponse = $this->actingAs($user)->postJson('/workorder/update_status', [
                'id' => $woId,
                'status' => 'done'
            ]);
            $statusResponse->assertStatus(200);
            $statusResponse->assertJson(['status' => true]);
            $this->assertEquals('done', Workorder::find($woId)->STATUS);

            // 6. Delete
            $deleteResponse = $this->actingAs($user)->postJson('/workorder/delete', [
                'id' => $woId
            ]);
            $deleteResponse->assertStatus(200);
            $deleteResponse->assertJson(['status' => true]);
            $this->assertNull(Workorder::find($woId));
            $this->assertNotNull(Workorder::withTrashed()->find($woId));

            // 7. Restore
            $restoreResponse = $this->actingAs($user)->postJson('/workorder/restore', [
                'id' => $woId
            ]);
            $restoreResponse->assertStatus(200);
            $restoreResponse->assertJson(['status' => true]);
            $this->assertNotNull(Workorder::find($woId));

        } finally {
            // Force cleanup
            Workorder::withTrashed()->where('ID_WORK_ORDER', $woId)->forceDelete();
        }
    }
}
