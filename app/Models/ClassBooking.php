<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassBooking extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_id',
        'member_id',
        'booking_date',
        'status',
        'attended_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'booking_date' => 'date',
            'attended_at' => 'datetime',
        ];
    }

    public function fitnessClass(): BelongsTo
    {
        return $this->belongsTo(FitnessClass::class, 'class_id');
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
}
