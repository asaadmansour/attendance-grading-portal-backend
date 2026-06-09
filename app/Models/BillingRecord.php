<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillingRecord extends Model
{
    protected $fillable = [
        'user_id',
        'cohort_id',
        'total_delivered_hours',
        'hourly_rate',
        'total_amount',
        'instructor_type',
        'status',
    ];

    protected $casts = [
        'total_delivered_hours' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cohort()
    {
        return $this->belongsTo(Cohort::class);
    }
}
