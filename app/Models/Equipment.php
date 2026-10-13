<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    use HasFactory;

    protected $table = 'equipment';

    protected $fillable = [
        'name',
        'category',
        'serial_number',
        'location',
        'purchase_date',
        'warranty_expiry',
        'last_service_date',
        'next_service_due',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'purchase_date' => 'date',
            'warranty_expiry' => 'date',
            'last_service_date' => 'date',
            'next_service_due' => 'date',
        ];
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'operational' => 'success',
            'needs_service' => 'warning',
            'under_maintenance' => 'info',
            'out_of_order' => 'danger',
            default => 'secondary',
        };
    }

    public function getIsServiceDueAttribute(): bool
    {
        return $this->next_service_due && $this->next_service_due->isPast();
    }
}
