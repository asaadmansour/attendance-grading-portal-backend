<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Cohort;
use App\Models\Track;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrackTest extends TestCase
{
    use RefreshDatabase;

    private User $bm;
    private Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bm = User::factory()->create(['role' => 'branch_manager']);
        $this->branch = Branch::create(['name' => 'Cairo Branch', 'manager_id' => $this->bm->id]);
    }

    public function test_branch_manager_can_create_a_track_in_their_branch(): void
    {
        $this->actingAs($this->bm)
            ->postJson('/api/v1/tracks', ['name' => 'Web Development'])
            ->assertCreated()
            ->assertJsonPath('data.name', 'Web Development');

        $this->assertDatabaseHas('tracks', [
            'name' => 'Web Development',
            'branch_id' => $this->branch->id,
        ]);
    }

    public function test_duplicate_track_name_in_the_same_branch_is_rejected(): void
    {
        Track::create(['branch_id' => $this->branch->id, 'name' => 'Web Development']);

        $this->actingAs($this->bm)
            ->postJson('/api/v1/tracks', ['name' => 'Web Development'])
            ->assertStatus(422);
    }

    public function test_branch_manager_can_rename_a_track(): void
    {
        $track = Track::create(['branch_id' => $this->branch->id, 'name' => 'Web']);

        $this->actingAs($this->bm)
            ->patchJson("/api/v1/tracks/{$track->id}", ['name' => 'Web Development'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Web Development');

        $this->assertSame('Web Development', $track->fresh()->name);
    }

    public function test_branch_manager_can_delete_a_track_without_cohorts(): void
    {
        $track = Track::create(['branch_id' => $this->branch->id, 'name' => 'Web']);

        $this->actingAs($this->bm)
            ->deleteJson("/api/v1/tracks/{$track->id}")
            ->assertOk();

        $this->assertDatabaseMissing('tracks', ['id' => $track->id]);
    }

    public function test_deleting_a_track_that_still_has_cohorts_is_refused(): void
    {
        $track = Track::create(['branch_id' => $this->branch->id, 'name' => 'Web']);
        Cohort::create(['track_id' => $track->id, 'name' => 'Intake 46', 'status' => 'active']);

        $this->actingAs($this->bm)
            ->deleteJson("/api/v1/tracks/{$track->id}")
            ->assertStatus(422);

        $this->assertDatabaseHas('tracks', ['id' => $track->id]);
    }

    public function test_track_admins_may_read_but_not_manage_tracks(): void
    {
        $ta = User::factory()->create(['role' => 'track_admin']);
        $track = Track::create(['branch_id' => $this->branch->id, 'name' => 'Web']);

        $this->actingAs($ta)->getJson('/api/v1/tracks')->assertOk();
        $this->actingAs($ta)->postJson('/api/v1/tracks', ['name' => 'Mobile'])->assertForbidden();
        $this->actingAs($ta)->patchJson("/api/v1/tracks/{$track->id}", ['name' => 'X'])->assertForbidden();
        $this->actingAs($ta)->deleteJson("/api/v1/tracks/{$track->id}")->assertForbidden();
    }

    public function test_guests_cannot_touch_tracks(): void
    {
        $this->getJson('/api/v1/tracks')->assertUnauthorized();
        $this->postJson('/api/v1/tracks', ['name' => 'Web'])->assertUnauthorized();
    }

    public function test_branch_manager_can_attach_and_detach_track_admins(): void
    {
        $track = Track::create(['branch_id' => $this->branch->id, 'name' => 'Web']);
        $admin = User::factory()->create(['role' => 'track_admin']);

        $this->actingAs($this->bm)
            ->postJson("/api/v1/tracks/{$track->id}/admins", ['user_ids' => [$admin->id]])
            ->assertOk();
        $this->assertDatabaseHas('track_admins', ['track_id' => $track->id, 'user_id' => $admin->id]);

        $this->actingAs($this->bm)
            ->deleteJson("/api/v1/tracks/{$track->id}/admins/{$admin->id}")
            ->assertOk();
        $this->assertDatabaseMissing('track_admins', ['track_id' => $track->id, 'user_id' => $admin->id]);
    }

    public function test_only_track_admins_can_be_attached(): void
    {
        $track = Track::create(['branch_id' => $this->branch->id, 'name' => 'Web']);
        $instructor = User::factory()->create(['role' => 'instructor']);

        $this->actingAs($this->bm)
            ->postJson("/api/v1/tracks/{$track->id}/admins", ['user_ids' => [$instructor->id]])
            ->assertStatus(422);
    }

    public function test_track_admins_cannot_manage_a_tracks_admins(): void
    {
        $ta = User::factory()->create(['role' => 'track_admin']);
        $track = Track::create(['branch_id' => $this->branch->id, 'name' => 'Web']);

        $this->actingAs($ta)
            ->postJson("/api/v1/tracks/{$track->id}/admins", ['user_ids' => [$ta->id]])
            ->assertForbidden();
    }

    // opening a cohort staffs it with its track's admins, and later changes keep it in step
    public function test_cohort_inherits_its_tracks_admins(): void
    {
        $track = Track::create(['branch_id' => $this->branch->id, 'name' => 'Web']);
        $a = User::factory()->create(['role' => 'track_admin']);
        $b = User::factory()->create(['role' => 'track_admin']);
        $track->admins()->attach($a->id);

        $cohort = Cohort::create(['track_id' => $track->id, 'name' => 'Intake 46', 'status' => 'active']);

        // create via the endpoint to exercise the inheritance in the controller
        $this->actingAs($this->bm)
            ->postJson('/api/v1/cohorts', ['track_id' => $track->id, 'name' => 'Intake 47', 'status' => 'planned'])
            ->assertCreated();
        $planned = Cohort::where('name', 'Intake 47')->first();
        $this->assertEqualsCanonicalizing([$a->id], $planned->tas()->pluck('users.id')->all());

        // attaching another admin updates the live (non-completed) cohorts
        $this->actingAs($this->bm)
            ->postJson("/api/v1/tracks/{$track->id}/admins", ['user_ids' => [$b->id]])
            ->assertOk();
        $this->assertEqualsCanonicalizing([$a->id, $b->id], $cohort->fresh()->tas()->pluck('users.id')->all());

        // detaching removes them from the live cohorts too
        $this->actingAs($this->bm)
            ->deleteJson("/api/v1/tracks/{$track->id}/admins/{$a->id}")
            ->assertOk();
        $this->assertEqualsCanonicalizing([$b->id], $cohort->fresh()->tas()->pluck('users.id')->all());
    }
}
