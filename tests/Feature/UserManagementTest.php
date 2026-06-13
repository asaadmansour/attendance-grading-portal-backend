<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_branch_manager_can_update_a_track_admin(): void
    {
        $bm = User::factory()->create(['role' => 'branch_manager']);
        $admin = User::factory()->create(['role' => 'track_admin', 'name' => 'Old Name']);

        $this->actingAs($bm)
            ->patchJson("/api/v1/users/{$admin->id}", ['name' => 'New Name'])
            ->assertOk()
            ->assertJsonPath('data.name', 'New Name')
            ->assertJsonPath('data.role', 'track_admin');

        $this->assertSame('New Name', $admin->fresh()->name);
    }

    public function test_branch_manager_can_reset_a_track_admin_password(): void
    {
        $bm = User::factory()->create(['role' => 'branch_manager']);
        $admin = User::factory()->create(['role' => 'track_admin']);

        $this->actingAs($bm)
            ->patchJson("/api/v1/users/{$admin->id}", [
                'password' => 'newsecret123',
                'password_confirmation' => 'newsecret123',
            ])
            ->assertOk();

        $this->assertTrue(Hash::check('newsecret123', $admin->fresh()->password));
    }

    public function test_blank_password_leaves_it_unchanged(): void
    {
        $bm = User::factory()->create(['role' => 'branch_manager']);
        $admin = User::factory()->create(['role' => 'track_admin']);
        $before = $admin->password;

        $this->actingAs($bm)
            ->patchJson("/api/v1/users/{$admin->id}", ['name' => 'Renamed', 'password' => ''])
            ->assertOk();

        $this->assertSame($before, $admin->fresh()->password);
    }

    public function test_branch_manager_can_delete_a_track_admin(): void
    {
        $bm = User::factory()->create(['role' => 'branch_manager']);
        $admin = User::factory()->create(['role' => 'track_admin']);

        $this->actingAs($bm)
            ->deleteJson("/api/v1/users/{$admin->id}")
            ->assertOk();

        $this->assertDatabaseMissing('users', ['id' => $admin->id]);
    }

    public function test_branch_manager_can_register_an_instructor(): void
    {
        $bm = User::factory()->create(['role' => 'branch_manager']);

        $this->actingAs($bm)
            ->postJson('/api/v1/auth/register', [
                'name' => 'New Instructor',
                'email' => 'instructor@iti.gov.eg',
                'role' => 'instructor',
                'password' => 'secret123',
                'password_confirmation' => 'secret123',
            ])
            ->assertCreated();

        $this->assertDatabaseHas('users', [
            'email' => 'instructor@iti.gov.eg',
            'role' => 'instructor',
            'created_by' => $bm->id,
        ]);
    }

    // the BM manages instructors just like a track admin does
    public function test_branch_manager_can_manage_instructors(): void
    {
        $bm = User::factory()->create(['role' => 'branch_manager']);
        $instructor = User::factory()->create(['role' => 'instructor']);

        $this->actingAs($bm)
            ->patchJson("/api/v1/users/{$instructor->id}", ['name' => 'X'])
            ->assertOk();
        $this->actingAs($bm)
            ->deleteJson("/api/v1/users/{$instructor->id}")
            ->assertOk();
    }

    public function test_email_must_stay_unique_on_update(): void
    {
        $bm = User::factory()->create(['role' => 'branch_manager']);
        $taken = User::factory()->create(['email' => 'taken@iti.gov.eg']);
        $admin = User::factory()->create(['role' => 'track_admin']);

        $this->actingAs($bm)
            ->patchJson("/api/v1/users/{$admin->id}", ['email' => 'taken@iti.gov.eg'])
            ->assertStatus(422);
    }

    // the same generic policy gives a track admin CRUD over their instructors/students
    public function test_track_admin_can_manage_instructors_but_not_track_admins(): void
    {
        $ta = User::factory()->create(['role' => 'track_admin']);
        $instructor = User::factory()->create(['role' => 'instructor']);
        $peer = User::factory()->create(['role' => 'track_admin']);

        $this->actingAs($ta)
            ->patchJson("/api/v1/users/{$instructor->id}", ['name' => 'Y'])
            ->assertOk();
        $this->actingAs($ta)
            ->deleteJson("/api/v1/users/{$peer->id}")
            ->assertForbidden();
    }

    public function test_instructors_cannot_manage_users(): void
    {
        $instructor = User::factory()->create(['role' => 'instructor']);
        $target = User::factory()->create(['role' => 'instructor']);

        $this->actingAs($instructor)
            ->patchJson("/api/v1/users/{$target->id}", ['name' => 'Z'])
            ->assertForbidden();
    }

    public function test_guests_cannot_manage_users(): void
    {
        $target = User::factory()->create(['role' => 'instructor']);

        $this->patchJson("/api/v1/users/{$target->id}", ['name' => 'Z'])->assertUnauthorized();
        $this->deleteJson("/api/v1/users/{$target->id}")->assertUnauthorized();
    }
}
