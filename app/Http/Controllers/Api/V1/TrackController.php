<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Track;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TrackController extends Controller
{
    // tracks the BM picks from when opening a cohort, each with the admins that run it
    public function index()
    {
        return $this->ok(Track::with('admins:id,name,email')->get(['id', 'name']));
    }

    public function store(Request $request)
    {
        $branch = $this->branchFor($request);

        $data = $request->validate([
            'name' => [
                'required', 'string', 'min:1', 'max:255',
                Rule::unique('tracks', 'name')->where('branch_id', $branch->id),
            ],
        ]);

        $track = Track::create(['branch_id' => $branch->id, 'name' => $data['name']]);

        return $this->ok($track->only('id', 'name'), 'Track created', 201);
    }

    public function update(Request $request, Track $track)
    {
        $data = $request->validate([
            'name' => [
                'required', 'string', 'min:1', 'max:255',
                Rule::unique('tracks', 'name')->where('branch_id', $track->branch_id)->ignore($track->id),
            ],
        ]);

        $track->update($data);

        return $this->ok($track->only('id', 'name'), 'Track updated');
    }

    public function destroy(Track $track)
    {
        // deleting a track cascades to its cohorts and everything under them, so refuse while any exist
        if ($track->cohorts()->exists()) {
            return $this->fail('This track still has cohorts. Delete them first.', 422);
        }

        $track->delete();

        return $this->ok(null, 'Track deleted');
    }

    // attach one or more existing track admins to the track
    public function attachAdmins(Request $request, Track $track)
    {
        $data = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => Rule::exists('users', 'id')->where('role', 'track_admin'),
        ]);

        $track->admins()->syncWithoutDetaching($data['user_ids']);
        $this->syncCohortStaff($track);

        return $this->ok($track->admins()->get(['users.id', 'users.name', 'users.email']));
    }

    // remove a track admin from the track
    public function detachAdmin(Track $track, User $user)
    {
        $track->admins()->detach($user->id);
        $this->syncCohortStaff($track);

        return $this->ok(null, 'Track admin removed');
    }

    // the branch a BM manages; falls back to the only branch in single-branch setups
    private function branchFor(Request $request): Branch
    {
        $branch = Branch::where('manager_id', $request->user()->id)->first() ?? Branch::first();

        abort_if(! $branch, 422, 'No branch is available to attach the track to.');

        return $branch;
    }

    // a cohort is staffed by its track's admins; keep the live cohorts in step when they change
    private function syncCohortStaff(Track $track): void
    {
        $adminIds = $track->admins()->pluck('users.id');

        $track->cohorts()->where('status', '!=', 'completed')->get()
            ->each(fn (\App\Models\Cohort $cohort) => $cohort->tas()->sync($adminIds));
    }
}
