<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FitnessClass extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'trainer_id',
        'room',
        'capacity',
        'day_of_week',
        'start_time',
        'end_time',
        'difficulty_level',
        'color_tag',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function trainer(): BelongsTo
    {
        return $this->belongsTo(Trainer::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(ClassBooking::class, 'class_id');
    }

    public function upcomingBookings(): HasMany
    {
        return $this->hasMany(ClassBooking::class, 'class_id')
            ->whereDate('booking_date', '>=', now()->toDateString())
            ->where('status', 'booked');
    }

    public function getSeatsBookedCountForDate(string $date): int
    {
        return $this->bookings()
            ->where('booking_date', $date)
            ->whereIn('status', ['booked', 'attended'])
            ->count();
    }

    public function getAvailableSeatsForDate(string $date): int
    {
        $booked = $this->getSeatsBookedCountForDate($date);
        return max(0, $this->capacity - $booked);
    }
}
