<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExcuseRequest extends Model
{
    use HasFactory;

    public const DEDUCTION_EXCUSED = 5;
    public const DEDUCTION_UNEXCUSED = 25;

    protected $fillable = [
        'attendance_id',
        'student_id',
        'reason',
        'status',
        'reviewed_by',
    ];
}
