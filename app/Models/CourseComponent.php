<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseComponent extends Model
{
    protected $fillable = [
        'course_id',
        'component_type',
        'weight',
        'raw_max',
    ];

    protected function casts(): array
    {
        return [
            'weight' => 'decimal:2',
            'raw_max' => 'decimal:2',
        ];
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
