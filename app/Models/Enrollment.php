<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'cohort_id',
        'lab_group_id',
    ];

    public function labGroup()
    {
        return $this->belongsTo(LabGroup::class);
    }
}
