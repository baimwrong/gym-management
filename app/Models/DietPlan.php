<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DietPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'trainer_id',
        'member_id',
        'target_calories',
        'protein_grams',
        'carbs_grams',
        'fats_grams',
        'meals',
        'is_template',
    ];

    protected function casts(): array
    {
        return [
            'target_calories' => 'integer',
            'protein_grams' => 'integer',
            'carbs_grams' => 'integer',
            'fats_grams' => 'integer',
            'meals' => 'array', // PostgreSQL jsonb meals
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
