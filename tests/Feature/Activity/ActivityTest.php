<?php

namespace Tests\Feature\Activity;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ActivityTest extends TestCase
{
    protected function getAdminUser(): User
    {
        return User::find(2) ?? User::first();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/activity')->assertRedirect('/login');
    }

    public function test_admin_can_view_activity_index_and_result(): void
    {
        $user = $this->getAdminUser();

        $resIndex = $this->actingAs($user)->get('/activity');
        $resIndex->assertStatus(200);
        $resIndex->assertSee('Aktifitas Lapangan');

        $resResult = $this->actingAs($user)->post('/activity/result', [
            'tgl' => date('Y-m-d'),
        ]);
        $resResult->assertStatus(200);
        $resResult->assertSee('Daftar Work Order Aktifitas Lapangan');
    }

    public function test_admin_can_view_form_and_list_detail_and_update_status(): void
    {
        $user = $this->getAdminUser();
        $wo = DB::table('t_work_order')->whereNull('DELETED_AT')->first();

        if ($wo) {
            // Form detail
            $resForm = $this->actingAs($user)->get('/activity/form_detail/' . $wo->ID_WORK_ORDER);
            $resForm->assertStatus(200);
            $resForm->assertSee('Input Laporan Aktifitas');

            // List detail
            $resList = $this->actingAs($user)->get('/activity/list_detail/' . $wo->ID_WORK_ORDER);
            $resList->assertStatus(200);
            $resList->assertSee('Daftar Laporan Aktifitas');

            // Update status
            $oldStatus = $wo->STATUS;
            $newStatus = $oldStatus === 'progress' ? 'open' : 'progress';

            $resUpdate = $this->actingAs($user)->postJson('/activity/update_status', [
                'id' => $wo->ID_WORK_ORDER,
                'status' => $newStatus,
            ]);
            $resUpdate->assertStatus(200);
            $resUpdate->assertJson(['status' => true]);

            // Revert status
            $this->actingAs($user)->postJson('/activity/update_status', [
                'id' => $wo->ID_WORK_ORDER,
                'status' => $oldStatus,
            ])->assertJson(['status' => true]);
        }
    }
}
