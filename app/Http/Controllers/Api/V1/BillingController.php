<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\BillingRecord;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    public function index(Request $request)
    {
        $query = BillingRecord::with('user:id,name,email', 'cohort:id,name')
            ->when($request->input('cohort_id'), fn ($q, $id) => $q->where('cohort_id', $id))
            ->when($request->input('instructor_type'), fn ($q, $type) => $q->where('instructor_type', $type))
            ->when($request->input('status'), fn ($q, $status) => $q->where('status', $status));

        return response()->json($query->paginate($request->input('per_page', 15)));
    }

    public function forward(Request $request)
    {
        $count = BillingRecord::where('status', 'pending')->update(['status' => 'forwarded']);

        return response()->json([
            'message' => 'Forwarded to accounting',
            'forwarded' => $count,
        ]);
    }
}
