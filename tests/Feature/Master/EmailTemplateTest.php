<?php

namespace Tests\Feature\Master;

use App\Models\User;
use App\Models\EmailTemplate;
use Tests\TestCase;

class EmailTemplateTest extends TestCase
{
    protected function getAdminUser(): User
    {
        return User::find(2) ?? User::first();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/master/email')->assertRedirect('/login');
    }

    public function test_admin_can_view_email_template_index_and_table(): void
    {
        $user = $this->getAdminUser();

        $resIndex = $this->actingAs($user)->get('/master/email');
        $resIndex->assertStatus(200);
        $resIndex->assertSee('Email Templates');

        $resTable = $this->actingAs($user)->postJson('/master/email/table');
        $resTable->assertStatus(200);
        $resTable->assertJson(['status' => true]);
        $this->assertArrayHasKey('dataTables', $resTable->json());
    }

    public function test_admin_can_create_edit_duplicate_and_delete_template(): void
    {
        $user = $this->getAdminUser();
        $slug = 'test_template_' . time();

        // 1. Create form
        $resCreate = $this->actingAs($user)->get('/master/email/create');
        $resCreate->assertStatus(200);
        $resCreate->assertSee('Basic Information');

        // 2. Store
        $resStore = $this->actingAs($user)->postJson('/master/email/store', [
            'template_name' => 'Automated Test Template',
            'template_slug' => $slug,
            'subject' => 'Welcome to {{site_name}}',
            'email_body' => '<p>Hello {{user_name}}</p>',
            'variables' => 'site_name, user_name',
            'category' => 'test',
            'is_active' => 1,
        ]);
        $resStore->assertStatus(200);
        $resStore->assertJson(['status' => true]);
        $templateId = $resStore->json('id');
        $this->assertNotNull($templateId);

        // 3. Edit form
        $resEdit = $this->actingAs($user)->get('/master/email/edit?id=' . $templateId);
        $resEdit->assertStatus(200);
        $resEdit->assertSee('Basic Information');

        // 4. Update
        $resUpdate = $this->actingAs($user)->postJson('/master/email/update', [
            'id' => $templateId,
            'template_name' => 'Automated Test Template Updated',
            'template_slug' => $slug,
            'subject' => 'Updated Welcome to {{site_name}}',
            'email_body' => '<p>Updated Hello {{user_name}}</p>',
            'variables' => 'site_name, user_name',
            'category' => 'test',
            'is_active' => 1,
        ]);
        $resUpdate->assertStatus(200);
        $resUpdate->assertJson(['status' => true]);

        // 5. Preview
        $resPreview = $this->actingAs($user)->get('/master/email/preview?id=' . $templateId);
        $resPreview->assertStatus(200);
        $resPreview->assertSee('Updated Welcome to {{site_name}}');

        // 6. Duplicate
        $resDup = $this->actingAs($user)->postJson('/master/email/duplicate', [
            'id' => $templateId,
        ]);
        $resDup->assertStatus(200);
        $resDup->assertJson(['status' => true]);
        $dupId = $resDup->json('id');

        // 7. Cleanup duplicate & original
        if ($dupId) {
            $this->actingAs($user)->postJson('/master/email/delete', ['id' => $dupId])
                ->assertJson(['status' => true]);
        }
        $this->actingAs($user)->postJson('/master/email/delete', ['id' => $templateId])
            ->assertJson(['status' => true]);
    }
}
