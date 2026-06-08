<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cohort extends Model
{
    protected $fillable = [
        'track_id',
        'name',
        'status',
        'created_by',
    ];

    public function track()
    {
        return $this->belongsTo(Track::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function courses()
    {
        return $this->hasMany(Course::class);
    }

    public function labGroups()
    {
        return $this->hasMany(LabGroup::class);
    }

    // staff (TAs) assigned to this cohort
    public function tas()
    {
        return $this->belongsToMany(User::class, 'cohort_admins')->withTimestamps();
    }
}
