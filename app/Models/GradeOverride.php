<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GradeOverride extends Model
{
    protected $fillable = ['note','new_value'];
    public function componentGrade(){
        return $this->belongsTo(ComponentGrade::class);
    }

    public function overriddenBy(){
        return $this->belongsTo(User::class,'overridden_by');
    }
}
