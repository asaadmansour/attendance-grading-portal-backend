<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Engagement extends Model
{
    use HasFactory;

    protected $fillable = [
        'cohort_id',
        'instructor_id',
    ];

    public function cohort()
    {
        return $this->belongsTo(Cohort::class, 'cohort_id');
    }

    public function sessions()
    {
        return $this->hasMany(Session::class);
    }
}
