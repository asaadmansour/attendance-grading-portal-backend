<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEngagementRequest;
use App\Models\Engagement;
use App\Models\User;
use Illuminate\Support\Carbon;

class EngagementController extends Controller
{
    public function store(StoreEngagementRequest $request)
    {
        $engagement = Engagement::create($request->validated());

        return $this->ok($engagement, 'Engagement created', 201);
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
