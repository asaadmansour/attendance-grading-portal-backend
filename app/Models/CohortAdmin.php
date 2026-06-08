<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// pivot: which users (TAs) staff which cohort
class CohortAdmin extends Model
{
    protected $fillable = [
        'cohort_id',
        'user_id',
    ];

    public function cohort()
    {
        return $this->belongsTo(Cohort::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
