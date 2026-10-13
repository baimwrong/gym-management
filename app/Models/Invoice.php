<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'member_id',
        'user_id',
        'customer_name',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'payment_status',
        'payment_method',
        'invoice_type',
        'paid_at',
        'due_date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'float',
            'tax_amount' => 'float',
            'discount_amount' => 'float',
            'total_amount' => 'float',
            'paid_at' => 'datetime',
            'due_date' => 'date',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->payment_status) {
            'paid' => 'success',
            'partial' => 'warning',
            'unpaid' => 'danger',
            'refunded' => 'secondary',
            default => 'info',
        };
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->payment_status === 'unpaid' && $this->due_date && $this->due_date->isPast();
    }
}
