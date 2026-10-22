<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use App\Services\JwtService;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ApiTest extends TestCase
{
    protected function getUser(): User
    {
        return User::where('id', 2)->first() ?? User::where('active', 1)->first();
    }

    protected function getAuthToken(User $user): string
    {
        $jwtService = app(JwtService::class);
        $user->roles_id = 2;
        $user->type_auth = 1;

        return $jwtService->generateAccessToken($user);
    }

    public function test_public_auth_login_validation(): void
    {
        $response = $this->postJson('/api/v1/auth/login', []);
        $response->assertStatus(422)
            ->assertJson(['status' => false]);
    }

    public function test_public_auth_login_invalid_credentials(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'identity' => 'nonexistent_user_12345',
            'password' => 'wrong_password',
        ]);
        $response->assertStatus(401)
            ->assertJson(['status' => false]);
    }

    public function test_protected_routes_require_token(): void
    {
        $this->getJson('/api/v1/auth/profile')
            ->assertStatus(401)
            ->assertJson(['status' => false]);

        $this->getJson('/api/v1/dashboard')
            ->assertStatus(401)
            ->assertJson(['status' => false]);

        $this->getJson('/api/v1/workorders')
            ->assertStatus(401)
            ->assertJson(['status' => false]);

        $this->getJson('/api/v1/sync/pull')
            ->assertStatus(401)
            ->assertJson(['status' => false]);
    }

    public function test_user_can_access_profile_with_token(): void
    {
        $user = $this->getUser();
        $token = $this->getAuthToken($user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/auth/profile');

        $response->assertStatus(200)
            ->assertJson(['status' => true])
            ->assertJsonPath('data.username', $user->username);
    }

    public function test_user_can_access_dashboard_with_token(): void
    {
        $user = $this->getUser();
        $token = $this->getAuthToken($user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/dashboard');

        $response->assertStatus(200)
            ->assertJson(['status' => true]);

        $this->assertArrayHasKey('counts', $response->json('data'));
        $this->assertArrayHasKey('recent_wo', $response->json('data'));
        $this->assertArrayHasKey('monthly', $response->json('data'));
        $this->assertArrayHasKey('weekly', $response->json('data'));
    }

    public function test_user_can_access_master_data_with_token(): void
    {
        $user = $this->getUser();
        $token = $this->getAuthToken($user);

        $headers = ['Authorization' => 'Bearer ' . $token];

        $resPelabuhan = $this->withHeaders($headers)->getJson('/api/v1/master/pelabuhan');
        $resPelabuhan->assertStatus(200)->assertJson(['status' => true]);

        $resKrk = $this->withHeaders($headers)->getJson('/api/v1/master/krk');
        $resKrk->assertStatus(200)->assertJson(['status' => true]);

        $resSatuan = $this->withHeaders($headers)->getJson('/api/v1/master/satuan');
        $resSatuan->assertStatus(200)->assertJson(['status' => true]);

        $resKapal = $this->withHeaders($headers)->getJson('/api/v1/master/kapal');
        $resKapal->assertStatus(200)->assertJson(['status' => true]);

        $resClient = $this->withHeaders($headers)->getJson('/api/v1/master/client');
        $resClient->assertStatus(200)->assertJson(['status' => true]);

        $resDermaga = $this->withHeaders($headers)->getJson('/api/v1/master/dermaga');
        $resDermaga->assertStatus(200)->assertJson(['status' => true]);

        $resAlat = $this->withHeaders($headers)->getJson('/api/v1/master/alat');
        $resAlat->assertStatus(200)->assertJson(['status' => true]);

        $resAktifitas = $this->withHeaders($headers)->getJson('/api/v1/master/aktifitas');
        $resAktifitas->assertStatus(200)->assertJson(['status' => true]);
    }

    public function test_user_can_access_workorders_list_with_token(): void
    {
        $user = $this->getUser();
        $token = $this->getAuthToken($user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/workorders');

        $response->assertStatus(200)
            ->assertJson(['status' => true]);

        $this->assertArrayHasKey('data', $response->json());
        $this->assertArrayHasKey('meta', $response->json());
    }

    public function test_user_can_access_notifications_with_token(): void
    {
        $user = $this->getUser();
        $token = $this->getAuthToken($user);

        $headers = ['Authorization' => 'Bearer ' . $token];

        $resList = $this->withHeaders($headers)->getJson('/api/v1/notifications');
        $resList->assertStatus(200)->assertJson(['status' => true]);

        $resUnread = $this->withHeaders($headers)->getJson('/api/v1/notifications/unread-count');
        $resUnread->assertStatus(200)->assertJson(['status' => true]);
        $this->assertArrayHasKey('count', $resUnread->json('data'));
    }

    public function test_user_can_pull_sync_with_token(): void
    {
        $user = $this->getUser();
        $token = $this->getAuthToken($user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/sync/pull');

        $response->assertStatus(200)
            ->assertJson(['status' => true]);

        $this->assertArrayHasKey('server_time', $response->json('data'));
        $this->assertArrayHasKey('workorders', $response->json('data'));
        $this->assertArrayHasKey('reports', $response->json('data'));
        $this->assertArrayHasKey('master', $response->json('data'));
    }

    public function test_refresh_token_flow(): void
    {
        $user = $this->getUser();
        $jwtService = app(JwtService::class);
        $refreshToken = $jwtService->generateRefreshToken($user->id, 'Test Device');

        $response = $this->postJson('/api/v1/auth/refresh', [
            'refresh_token' => $refreshToken,
        ]);

        $response->assertStatus(200)
            ->assertJson(['status' => true]);

        $this->assertArrayHasKey('access_token', $response->json('data'));
        $this->assertArrayHasKey('refresh_token', $response->json('data'));

        // Clean up
        $newRefreshToken = $response->json('data.refresh_token');
        $jwtService->revokeRefreshToken($newRefreshToken);
    }
}
