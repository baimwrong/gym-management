<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'check_in_time',
        'check_out_time',
        'check_in_method',
        'checked_in_by_user_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'check_in_time' => 'datetime',
            'check_out_time' => 'datetime',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function checkedInBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_in_by_user_id');
    }

    public function getDurationMinutesAttribute(): ?int
    {
        if (!$this->check_out_time) {
            return null;
        }
        return $this->check_in_time->diffInMinutes($this->check_out_time);
    }

    public function getIsCurrentlyInsideAttribute(): bool
    {
        return $this->check_out_time === null;
    }

    // Scopes
    public function scopeCurrentlyInside($query)
    {
        return $query->whereNull('check_out_time')
                     ->whereDate('check_in_time', now()->toDateString());
    }

    public function scopeToday($query)
    {
        return $query->whereDate('check_in_time', now()->toDateString());
    }
}
