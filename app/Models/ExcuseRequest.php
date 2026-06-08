<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExcuseRequest extends Model
{
    use HasFactory;

    const STATUS_REQUESTED = 'requested';
    const STATUS_APPROVED  = 'approved';
    const STATUS_REJECTED  = 'rejected';

    const DEDUCTION_UNEXCUSED = 25;
    const DEDUCTION_EXCUSED   = 5;

    protected $fillable = [
        'attendance_id',
        'student_id',
        'reason',
        'attachment_path',
        'status',
        'reviewed_by',
    ];

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_REQUESTED;
    }
}