<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEngagementRequest;
use App\Http\Requests\UpdateEngagementRequest;
use App\Models\Engagement;
use App\Models\User;
use Illuminate\Support\Carbon;

class EngagementController extends Controller
{
    public function index()
    {
        return $this->ok(Engagement::with('instructor')->get());
    }

    public function show(Engagement $engagement)
    {
        return $this->ok($engagement->load('instructor'));
    }

    public function store(StoreEngagementRequest $request)
    {
        $engagement = Engagement::create($request->validated());

        return $this->ok($engagement, 'Engagement created', 201);
    }

    public function update(UpdateEngagementRequest $request, Engagement $engagement)
    {
        $engagement->update($request->validated());

        return $this->ok($engagement, 'Engagement updated');
    }

    public function destroy(Engagement $engagement)
    {
        $engagement->delete();

        return $this->ok(null, 'Engagement deleted');
    }

    // a person's access window: earliest start to latest end across their engagements
    public function accessWindow(User $user)
    {
        $start = $user->engagements()->min('start_date');
        $end = $user->engagements()->max('end_date');

        return $this->ok([
            'user_id' => $user->id,
            'start' => $start ? Carbon::parse($start)->toDateString() : null,
            'end' => $end ? Carbon::parse($end)->toDateString() : null,
        ]);
    }
}
