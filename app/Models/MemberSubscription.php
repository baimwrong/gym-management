<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class MemberSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'plan_id',
        'start_date',
        'end_date',
        'status',
        'price_paid',
        'remaining_pt_sessions',
        'is_auto_renew',
        'frozen_at',
        'unfreeze_date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'price_paid' => 'float',
            'is_auto_renew' => 'boolean',
            'frozen_at' => 'datetime',
            'unfreeze_date' => 'date',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(MembershipPlan::class, 'plan_id');
    }

    public function getDaysRemainingAttribute(): int
    {
        if ($this->end_date->isPast()) {
            return 0;
        }
        return Carbon::now()->diffInDays($this->end_date, false);
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->end_date->isPast() || $this->status === 'expired';
    }
}
