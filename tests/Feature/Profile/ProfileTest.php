<?php

namespace Tests\Feature\Profile;

use App\Models\User;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    protected function getAdminUser(): User
    {
        return User::find(2) ?? User::first();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/profile')->assertRedirect('/login');
        $this->get('/profile/edit')->assertRedirect('/login');
    }

    public function test_admin_can_view_profile_index_and_edit(): void
    {
        $user = $this->getAdminUser();

        $resIndex = $this->actingAs($user)->get('/profile');
        $resIndex->assertStatus(200);
        $resIndex->assertSee($user->username);
        $resIndex->assertSee('Profile Info');

        $resEdit = $this->actingAs($user)->get('/profile/edit');
        $resEdit->assertStatus(200);
        $resEdit->assertSee('Personal Details');
        $resEdit->assertSee('Change Password');

        $resDetail = $this->actingAs($user)->get('/profile/detail/' . $user->id);
        $resDetail->assertStatus(200);
        $resDetail->assertSee($user->username);
    }

    public function test_admin_can_update_profile_info(): void
    {
        $user = $this->getAdminUser();
        $oldUsername = $user->username;

        $resStore = $this->actingAs($user)->postJson('/profile/store_info', [
            'model_id' => $user->id,
            'name' => $oldUsername,
            'phone' => '081234567890',
        ]);

        $resStore->assertStatus(200);
        $resStore->assertJson(['status' => true]);
    }
}
