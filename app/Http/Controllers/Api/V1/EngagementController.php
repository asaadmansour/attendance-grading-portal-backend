<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEngagementRequest;
use App\Http\Requests\UpdateEngagementRequest;
use App\Models\BillingRecord;
use App\Models\Engagement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class EngagementController extends Controller
{
    public function index(Request $request)
    {
        $query = Engagement::with('instructor');

        // a TA only sees engagements in their own cohorts; the BM sees the lot
        if ($request->user()->role === 'track_admin') {
            $query->whereHas('cohort.tas', fn ($q) => $q->whereKey($request->user()->id));
        }

        return $this->ok($query->get());
    }

    public function show(Request $request, Engagement $engagement)
    {
        $this->authorizeAccess($request, $engagement);

        return $this->ok($engagement->load('instructor'));
    }

    public function store(StoreEngagementRequest $request)
    {
        $engagement = DB::transaction(function () use ($request) {
            $engagement = Engagement::create($request->validated());
            $this->syncBilling($engagement);
            return $engagement;
        });

        return $this->ok($engagement, 'Engagement created', 201);
    }

    public function update(UpdateEngagementRequest $request, Engagement $engagement)
    {
        $this->authorizeAccess($request, $engagement);

        DB::transaction(function () use ($request, $engagement) {
            $engagement->update($request->validated());

            if ($request->hasAny(['instructor_id', 'cohort_id', 'scheduled_hours_per_session'])) {
                $this->syncBilling($engagement);
            }
        });

        return $this->ok($engagement, 'Engagement updated');
    }

    public function destroy(Request $request, Engagement $engagement)
    {
        $this->authorizeAccess($request, $engagement);

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

    private function syncBilling(Engagement $engagement): void
    {
        $user = $engagement->instructor;

        $totalHours = Engagement::where('instructor_id', $user->id)
            ->where('cohort_id', $engagement->cohort_id)
            ->sum('scheduled_hours_per_session');

        $isExternal = $user->compensation_type === 'external';

        $billing = BillingRecord::firstOrNew([
            'user_id' => $user->id,
            'cohort_id' => $engagement->cohort_id,
        ]);

        if ($billing->status === 'forwarded') {
            return;
        }

        $rate = $user->hourly_rate ?? 0;

        $billing->fill([
            'total_delivered_hours' => $totalHours,
            'hourly_rate' => $rate,
            'total_amount' => $totalHours * $rate,
            'instructor_type' => $isExternal ? 'external' : 'internal',
            'status' => 'pending',
        ])->save();
    }

    // the BM can touch any engagement, a TA only the ones in cohorts they run
    private function authorizeAccess(Request $request, Engagement $engagement): void
    {
        $user = $request->user();

        abort_unless(
            $user->role === 'branch_manager' || $engagement->cohort?->isManagedBy($user),
            403,
            'Forbidden'
        );
    }
}
