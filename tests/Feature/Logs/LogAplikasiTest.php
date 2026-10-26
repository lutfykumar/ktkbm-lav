<?php

namespace Tests\Feature\Logs;

use App\Models\User;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class LogAplikasiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Ensure log file exists with dummy sample content
        $logPath = storage_path('logs/laravel.log');
        if (!File::exists($logPath) || File::size($logPath) === 0) {
            File::put($logPath, "[2026-10-26 12:00:00] local.INFO: Test log message for testing suite\n[2026-10-26 12:01:00] local.ERROR: Test error message\nStack trace line 1\n");
        }
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/logs/aplikasi');
        $response->assertRedirect('/login');
    }

    public function test_admin_can_view_application_logs_index(): void
    {
        $admin = User::where('active', 1)->first();
        $this->assertNotNull($admin);

        $response = $this->actingAs($admin)->get('/logs/aplikasi');

        $response->assertStatus(200);
        $response->assertSee('Application Logs');
        $response->assertSee('storage/logs/laravel.log');
    }

    public function test_admin_can_fetch_logs_table_json(): void
    {
        $admin = User::where('active', 1)->first();
        $this->assertNotNull($admin);

        $response = $this->actingAs($admin)->getJson('/logs/aplikasi/table');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'data',
            'total',
        ]);
        $this->assertTrue($response->json('status'));
    }

    public function test_admin_can_download_application_log(): void
    {
        $admin = User::where('active', 1)->first();
        $this->assertNotNull($admin);

        $response = $this->actingAs($admin)->get('/logs/aplikasi/download');

        $response->assertStatus(200);
        $response->assertHeader('content-disposition');
    }

    public function test_admin_can_clear_application_log(): void
    {
        $admin = User::where('active', 1)->first();
        $this->assertNotNull($admin);

        $response = $this->actingAs($admin)->postJson('/logs/aplikasi/clear');

        $response->assertStatus(200);
        $response->assertJson([
            'status' => true,
        ]);
    }
}
