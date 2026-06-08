<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LabGroup extends Model
{
    protected $fillable = [
        'cohort_id',
        'instructor_id',
        'name',
        'capacity',
    ];

    public function cohort()
    {
        return $this->belongsTo(Cohort::class);
    }

    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }
}
