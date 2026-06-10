<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ComponentGrade extends Model
{
    use SoftDeletes;

    protected $fillable = ['course_component_id', 'student_id', 'raw_score'];

    public function courseComponent() { return $this->belongsTo(CourseComponent::class); }
    public function student()         { return $this->belongsTo(User::class, 'student_id'); }
    public function enteredBy()       { return $this->belongsTo(User::class, 'entered_by'); }
}