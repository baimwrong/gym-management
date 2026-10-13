<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberHealthMetric extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'recorded_date',
        'weight_kg',
        'height_cm',
        'bmi',
        'body_fat_percentage',
        'muscle_mass_kg',
        'chest_cm',
        'waist_cm',
        'arms_cm',
        'thighs_cm',
        'notes',
        'recorded_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'recorded_date' => 'date',
            'weight_kg' => 'float',
            'height_cm' => 'float',
            'bmi' => 'float',
            'body_fat_percentage' => 'float',
            'muscle_mass_kg' => 'float',
            'chest_cm' => 'float',
            'waist_cm' => 'float',
            'arms_cm' => 'float',
            'thighs_cm' => 'float',
        ];
    }

    public static function booted(): void
    {
        static::saving(function ($metric) {
            if ($metric->weight_kg && $metric->height_cm && $metric->height_cm > 0) {
                $heightMeters = $metric->height_cm / 100.0;
                $metric->bmi = round($metric->weight_kg / ($heightMeters * $heightMeters), 1);
            }
        });
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_user_id');
    }

    public function getBmiCategoryAttribute(): string
    {
        if (!$this->bmi) {
            return 'N/A';
        }
        if ($this->bmi < 18.5) {
            return 'Underweight';
        }
        if ($this->bmi < 25) {
            return 'Normal weight';
        }
        if ($this->bmi < 30) {
            return 'Overweight';
        }
        return 'Obese';
    }
}
