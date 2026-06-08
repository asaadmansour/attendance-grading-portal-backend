<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class StudentTag extends Model
{
    use SoftDeletes;
    protected $fillable = ['tag_id','course_id'];
    public function student()    { return $this->belongsTo(User::class, 'student_id'); }
    public function assignedBy() { return $this->belongsTo(User::class, 'assigned_by'); }
    public function tag()        { return $this->belongsTo(Tag::class); }
    public function course()     { return $this->belongsTo(Course::class); }
}
