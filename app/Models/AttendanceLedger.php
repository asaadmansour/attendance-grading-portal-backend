<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceLedger extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'balance',
    ];

    protected $casts = [
        'balance' => 'integer',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function deduct(int $points): void
    {
        $this->decrement('balance', $points);
    }

    public function adjustDeduction(int $oldDeduction, int $newDeduction): void
    {
        $diff = $oldDeduction - $newDeduction;
        $this->increment('balance', $diff);
    }
}