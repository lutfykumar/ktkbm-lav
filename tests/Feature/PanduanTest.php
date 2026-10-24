<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class PanduanTest extends TestCase
{
    protected function getAdminUser(): User
    {
        return User::find(2) ?? User::first();
    }

    public function test_guest_is_redirected_from_panduan(): void
    {
        $response = $this->get('/panduan');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_access_panduan(): void
    {
        $user = $this->getAdminUser();

        $response = $this->actingAs($user)->get('/panduan');
        $response->assertStatus(200);
        $response->assertSee('Pusat Panduan Sistem Informasi TKBM Pelabuhan');
        $response->assertSee('Gambaran Umum & Alur Maker-Checker', false);
        $response->assertSee('Panduan Admin (Web Panel)');
        $response->assertSee('Panduan Pengawas (Mobile App)');
    }
}
