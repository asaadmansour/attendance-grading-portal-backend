<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\BillingRecord;
use App\Models\Engagement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class BillingController extends Controller
{
    // turn delivered hours into a billing line for each person
    public function run(Request $request)
    {
        $data = $request->validate([
            'cohort_id' => 'sometimes|integer|exists:cohorts,id',
        ]);

        // let Eloquent add up the delivered hours, then group per person per cohort
        $grouped = Engagement::with('instructor')
            ->whereNotNull('instructor_id')
            ->whereNotNull('cohort_id')
            ->when($data['cohort_id'] ?? null, fn ($q, $id) => $q->where('cohort_id', $id))
            ->withSum(['sessions as delivered' => fn ($q) => $q->where('is_delivered', true)], 'delivered_hours')
            ->get()
            ->groupBy(fn ($e) => $e->instructor_id.'-'.$e->cohort_id);

        foreach ($grouped as $engagements) {
            $first = $engagements->first();
            $user = $first->instructor;

            // no pay set up, nothing to bill
            if (! in_array($user->compensation_type, ['external', 'internal'], true)) {
                continue;
            }

            // already sent to accounting, so leave it be
            $line = BillingRecord::firstOrNew(['user_id' => $user->id, 'cohort_id' => $first->cohort_id]);
            if ($line->status === 'forwarded') {
                continue;
            }

            $hours = (float) $engagements->sum('delivered');
            $rate = (float) ($user->hourly_rate ?? 0);

            $line->fill([
                'total_delivered_hours' => $hours,
                'hourly_rate' => $rate,
                'total_amount' => $hours * $rate,
                'instructor_type' => $user->compensation_type,
                'status' => 'pending',
            ])->save();
        }

        return $this->ok(null, 'Billing records generated');
    }

    // the full bill, split into internal and external
    public function rollup(Request $request)
    {
        $records = BillingRecord::with('user:id,name', 'cohort:id,name')->get();

        return $this->ok($this->summarize($records) + ['records' => $records], 'Billing rollup');
    }

    // send the pending lines to accounting
    public function forward(Request $request)
    {
        $pending = BillingRecord::where('status', 'pending')->get();
        BillingRecord::whereKey($pending->pluck('id'))->update(['status' => 'forwarded']);

        return $this->ok($this->summarize($pending) + ['forwarded' => $pending->count()], 'Forwarded to accounting');
    }

    // external are paid by the hour, internal also get a salary counted once
    private function summarize(Collection $records): array
    {
        $external = $records->where('instructor_type', 'external');
        $internal = $records->where('instructor_type', 'internal');

        $externalAmount = round((float) $external->sum('total_amount'), 2);
        $internalHourly = round((float) $internal->sum('total_amount'), 2);
        $internalSalary = round((float) User::whereKey($internal->pluck('user_id')->unique())->sum('monthly_salary'), 2);

        return [
            'external' => [
                'total_delivered_hours' => round((float) $external->sum('total_delivered_hours'), 2),
                'total_amount' => $externalAmount,
            ],
            'internal' => [
                'total_delivered_hours' => round((float) $internal->sum('total_delivered_hours'), 2),
                'salary_amount' => $internalSalary,
                'total_amount' => round($internalHourly + $internalSalary, 2),
            ],
            'grand_total_amount' => round($externalAmount + $internalHourly + $internalSalary, 2),
        ];
    }
}
