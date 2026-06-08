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

    // the students in this group, read off their enrollment rows
    public function students()
    {
        return $this->belongsToMany(User::class, 'enrollments', 'lab_group_id', 'student_id');
    }
}
