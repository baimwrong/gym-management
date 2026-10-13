<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkoutPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'trainer_id',
        'member_id',
        'difficulty_level',
        'days_per_week',
        'routine_schedule',
        'is_template',
    ];

    protected function casts(): array
    {
        return [
            'days_per_week' => 'integer',
            'routine_schedule' => 'array', // PostgreSQL jsonb array of workout days & exercises
            'is_template' => 'boolean',
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
}
