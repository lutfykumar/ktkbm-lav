<?php

namespace Tests\Feature\Logs;

use App\Models\User;
use Tests\TestCase;

class LogsTest extends TestCase
{
    protected function getAdminUser(): User
    {
        return User::find(2) ?? User::first();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/logs/aktifitas')->assertRedirect('/login');
        $this->get('/logs/api')->assertRedirect('/login');
        $this->get('/logs/email')->assertRedirect('/login');
        $this->get('/logs/errors')->assertRedirect('/login');
    }

    public function test_admin_can_view_user_activity_logs(): void
    {
        $user = $this->getAdminUser();

        $resIndex = $this->actingAs($user)->get('/logs/aktifitas');
        $resIndex->assertStatus(200);
        $resIndex->assertSee('User Activity');

        $resTable = $this->actingAs($user)->postJson('/logs/aktifitas/table');
        $resTable->assertStatus(200);
        $resTable->assertJson(['status' => true]);
        $this->assertArrayHasKey('data', $resTable->json());
    }

    public function test_admin_can_view_api_logs(): void
    {
        $user = $this->getAdminUser();

        $resIndex = $this->actingAs($user)->get('/logs/api');
        $resIndex->assertStatus(200);
        $resIndex->assertSee('API Request');

        $resData = $this->actingAs($user)->postJson('/logs/api/get_data');
        $resData->assertStatus(200);
        $resData->assertJson(['status' => true]);
        $this->assertArrayHasKey('data', $resData->json());
    }

    public function test_admin_can_view_email_logs(): void
    {
        $user = $this->getAdminUser();

        $resIndex = $this->actingAs($user)->get('/logs/email');
        $resIndex->assertStatus(200);
        $resIndex->assertSee('Email Logs');

        $resTable = $this->actingAs($user)->postJson('/logs/email/table');
        $resTable->assertStatus(200);
        $resTable->assertJson(['status' => true]);
        $this->assertArrayHasKey('data', $resTable->json());
    }

    public function test_admin_can_view_error_logs(): void
    {
        $user = $this->getAdminUser();

        $resIndex = $this->actingAs($user)->get('/logs/errors');
        $resIndex->assertStatus(200);
        $resIndex->assertSee('Error Logs');

        $resData = $this->actingAs($user)->postJson('/logs/errors/get_data');
        $resData->assertStatus(200);
        $resData->assertJson(['status' => true]);
        $this->assertArrayHasKey('data', $resData->json());
    }
}
