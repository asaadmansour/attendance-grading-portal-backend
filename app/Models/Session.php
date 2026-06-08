<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Session extends Model
{
    use HasFactory;

    protected $table = 'training_sessions';

    protected $fillable = [
        'engagement_id',
        'session_date',
        'scheduled_hours',
        'is_delivered',
        'delivered_hours',
    ];

    protected $casts = [
        'session_date'    => 'date',
        'is_delivered'    => 'boolean',
        'scheduled_hours' => 'decimal:2',
        'delivered_hours' => 'decimal:2',
    ];

    public function engagement(): BelongsTo
    {
        return $this->belongsTo(Engagement::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }
}