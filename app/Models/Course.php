<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = [
        'cohort_id',
        'name',
        'total_points',
    ];

    public function cohort()
    {
        return $this->belongsTo(Cohort::class);
    }

    public function components()
    {
        return $this->hasMany(CourseComponent::class);
    }
}
