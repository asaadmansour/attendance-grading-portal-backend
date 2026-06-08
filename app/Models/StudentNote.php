<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class StudentNote extends Model
{
    use SoftDeletes;
    protected $fillable = ['student_id', 'body'];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
