<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCohortRequest;
use App\Http\Requests\UpdateCohortRequest;
use App\Models\Cohort;
use Illuminate\Http\Request;

class CohortController extends Controller
{
    public function index(Request $request)
    {
        $query = Cohort::with('track');

        // track admins only see the cohorts they run
        if ($request->user()->role === 'track_admin') {
            $query->whereHas('tas', fn ($q) => $q->whereKey($request->user()->id));
        }

        return $this->ok($query->get());
    }

    public function show(Request $request, Cohort $cohort)
    {
        if ($request->user()->role === 'track_admin'
            && ! $cohort->tas()->whereKey($request->user()->id)->exists()) {
            return $this->fail('Forbidden', 403);
        }

        return $this->ok($cohort->load('track', 'tas', 'courses.components', 'labGroups.instructor'));
    }

    public function store(StoreCohortRequest $request)
    {
        $cohort = Cohort::create([
            'track_id' => $request->track_id,
            'name' => $request->name,
            'status' => 'active',
            'created_by' => $request->user()->id,
        ]);

        $cohort->tas()->sync($request->input('ta_ids', []));

        return $this->ok($cohort->load('tas'), 'Cohort created', 201);
    }

    public function update(UpdateCohortRequest $request, Cohort $cohort)
    {
        $cohort->update($request->validated());

        return $this->ok($cohort, 'Cohort updated');
    }

    public function destroy(Cohort $cohort)
    {
        $cohort->delete();

        return $this->ok(null, 'Cohort deleted');
    }
}
