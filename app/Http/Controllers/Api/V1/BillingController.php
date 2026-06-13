<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\BillingRecord;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BillingController extends Controller
{
    public function index(Request $request)
    {
        $query = BillingRecord::with('user:id,name,email', 'cohort:id,name')
            ->when($request->input('cohort_id'), fn ($q, $id) => $q->where('cohort_id', $id))
            ->when($request->input('instructor_type'), fn ($q, $type) => $q->where('instructor_type', $type))
            ->when(
                $request->input('status'),
                fn ($q, $status) => $q->where('status', $status),
                fn ($q) => $q->where('status', 'pending')
            );

        return response()->json($query->paginate($request->input('per_page', 15)));
    }

    public function forward(): StreamedResponse
    {
        $records = BillingRecord::with('user:id,name,email', 'cohort:id,name')
            ->where('status', 'pending')
            ->get();

        BillingRecord::whereIn('id', $records->pluck('id'))->update(['status' => 'forwarded']);

        return new StreamedResponse(function () use ($records) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Instructor', 'Email', 'Cohort', 'Type', 'Hours', 'Rate', 'Amount']);

            foreach ($records as $record) {
                fputcsv($handle, [
                    $record->user->name ?? '',
                    $record->user->email ?? '',
                    $record->cohort->name ?? '',
                    $record->instructor_type,
                    $record->total_delivered_hours,
                    $record->hourly_rate,
                    $record->total_amount,
                ]);
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="billing-' . now()->format('Y-m-d') . '.csv"',
        ]);
    }
}
