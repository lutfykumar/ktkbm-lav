<?php

namespace Tests\Feature\Pengaturan;

use App\Models\User;
use Tests\TestCase;

class PengaturanTest extends TestCase
{
    protected function getAdminUser(): User
    {
        return User::find(2) ?? User::first();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/pengaturan/roles')->assertRedirect('/login');
        $this->get('/pengaturan/pengguna')->assertRedirect('/login');
        $this->get('/pengaturan/menu')->assertRedirect('/login');
        $this->get('/pengaturan/akses')->assertRedirect('/login');
        $this->get('/pengaturan/aplikasi')->assertRedirect('/login');
        $this->get('/pengaturan/cron')->assertRedirect('/login');
    }

    public function test_roles_screen_and_table(): void
    {
        $user = $this->getAdminUser();

        $resIndex = $this->actingAs($user)->get('/pengaturan/roles');
        $resIndex->assertStatus(200);
        $resIndex->assertSee('crudTable');

        $resTable = $this->actingAs($user)->getJson('/pengaturan/roles/table');
        $resTable->assertStatus(200);
        $this->assertIsArray($resTable->json());
    }

    public function test_pengguna_screen_and_table(): void
    {
        $user = $this->getAdminUser();

        $resIndex = $this->actingAs($user)->get('/pengaturan/pengguna');
        $resIndex->assertStatus(200);
        $resIndex->assertSee('crudTable');

        $resTable = $this->actingAs($user)->getJson('/pengaturan/pengguna/table');
        $resTable->assertStatus(200);
        $this->assertIsArray($resTable->json());
    }

    public function test_menu_screen(): void
    {
        $user = $this->getAdminUser();

        $resIndex = $this->actingAs($user)->get('/pengaturan/menu');
        $resIndex->assertStatus(200);
        $resIndex->assertSee('Atur Menu Sidebar');
    }

    public function test_akses_screen_and_table(): void
    {
        $user = $this->getAdminUser();

        $resIndex = $this->actingAs($user)->get('/pengaturan/akses');
        $resIndex->assertStatus(200);
        $resIndex->assertSee('crudTable');

        $resTable = $this->actingAs($user)->getJson('/pengaturan/akses/table');
        $resTable->assertStatus(200);
        $this->assertIsArray($resTable->json());
    }

    public function test_aplikasi_screen_and_get_settings(): void
    {
        $user = $this->getAdminUser();

        $resIndex = $this->actingAs($user)->get('/pengaturan/aplikasi');
        $resIndex->assertStatus(200);
        $resIndex->assertSee('Pengaturan Aplikasi');

        $resSettings = $this->actingAs($user)->getJson('/pengaturan/aplikasi/get_settings');
        $resSettings->assertStatus(200);
        $resSettings->assertJson(['status' => true]);
        $this->assertArrayHasKey('settings', $resSettings->json());
        $this->assertArrayHasKey('metadata', $resSettings->json());
    }

    public function test_cron_screen_get_data_and_statistics(): void
    {
        $user = $this->getAdminUser();

        $resIndex = $this->actingAs($user)->get('/pengaturan/cron');
        $resIndex->assertStatus(200);
        $resIndex->assertSee('Cron Job Management');

        $resData = $this->actingAs($user)->postJson('/pengaturan/cron/get_data');
        $resData->assertStatus(200);
        $this->assertIsArray($resData->json());

        $resStats = $this->actingAs($user)->getJson('/pengaturan/cron/statistics');
        $resStats->assertStatus(200);
        $resStats->assertJson(['status' => true]);
        $this->assertArrayHasKey('job_stats', $resStats->json());
    }
}
