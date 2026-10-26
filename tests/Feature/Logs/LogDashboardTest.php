<?php

namespace Tests\Feature\Logs;

use App\Models\User;
use Tests\TestCase;

class LogDashboardTest extends TestCase
{
    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/logs/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_admin_can_view_logs_dashboard(): void
    {
        $admin = User::where('active', 1)->first();
        $this->assertNotNull($admin);

        $response = $this->actingAs($admin)->get('/logs/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Logs & Analytics Dashboard');
        $response->assertSee('Aktivitas Hari Ini');
    }

    public function test_admin_can_fetch_realtime_metrics_json(): void
    {
        $admin = User::where('active', 1)->first();
        $this->assertNotNull($admin);

        $response = $this->actingAs($admin)->getJson('/logs/dashboard/realtime');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'metrics' => [
                'activity_today',
                'api_today',
                'email_sent',
                'unresolved_errors',
                'log_size_formatted',
            ],
            'recentData' => [
                'activities',
                'errors',
            ],
            'timestamp',
        ]);
        $this->assertTrue($response->json('status'));
    }
}
