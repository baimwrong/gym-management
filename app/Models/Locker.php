<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Locker extends Model
{
    use HasFactory;

    protected $fillable = [
        'locker_number',
        'zone',
        'status',
        'assigned_member_id',
        'assigned_at',
        'expires_at',
        'rental_fee_monthly',
    ];

    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
            'expires_at' => 'datetime',
            'rental_fee_monthly' => 'float',
        ];
    }

    public function assignedMember(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'assigned_member_id');
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'available' => 'success',
            'occupied' => 'primary',
            'maintenance' => 'warning',
            default => 'secondary',
        };
    }
}
