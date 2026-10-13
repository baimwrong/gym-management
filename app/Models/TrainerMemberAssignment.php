<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainerMemberAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'trainer_id',
        'member_id',
        'package_name',
        'total_sessions',
        'completed_sessions',
        'status',
        'start_date',
        'end_date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'total_sessions' => 'integer',
            'completed_sessions' => 'integer',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function trainer(): BelongsTo
    {
        return $this->belongsTo(Trainer::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function getRemainingSessionsAttribute(): int
    {
        return max(0, $this->total_sessions - $this->completed_sessions);
    }

    public function getProgressPercentageAttribute(): int
    {
        if ($this->total_sessions === 0) {
            return 0;
        }
        return (int) round(($this->completed_sessions / $this->total_sessions) * 100);
    }
}
