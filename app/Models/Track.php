<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Track extends Model
{
    protected $fillable = [
        'branch_id',
        'name',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function cohorts()
    {
        return $this->hasMany(Cohort::class);
    }
}
