<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Trainer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'full_name',
        'phone',
        'email',
        'specialties',
        'biography',
        'hourly_rate',
        'commission_rate_percentage',
        'certifications',
        'is_available',
    ];

    protected function casts(): array
    {
        return [
            'specialties' => 'array',
            'hourly_rate' => 'float',
            'commission_rate_percentage' => 'float',
            'is_available' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(TrainerMemberAssignment::class);
    }

    public function activeClients(): HasMany
    {
        return $this->hasMany(TrainerMemberAssignment::class)->where('status', 'active');
    }

    public function classes(): HasMany
    {
        return $this->hasMany(FitnessClass::class);
    }

    public function workoutPlans(): HasMany
    {
        return $this->hasMany(WorkoutPlan::class);
    }

    public function dietPlans(): HasMany
    {
        return $this->hasMany(DietPlan::class);
    }
}
