<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class MembershipPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'duration_type',
        'duration_count',
        'price',
        'signup_fee',
        'includes_classes',
        'includes_pt_sessions',
        'max_freeze_days',
        'features',
        'badge_color',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'float',
            'signup_fee' => 'float',
            'includes_classes' => 'boolean',
            'includes_pt_sessions' => 'integer',
            'max_freeze_days' => 'integer',
            'features' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public static function booted(): void
    {
        static::creating(function ($plan) {
            if (empty($plan->slug)) {
                $plan->slug = Str::slug($plan->name);
            }
        });
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(MemberSubscription::class, 'plan_id');
    }

    public function getFormattedDurationAttribute(): string
    {
        return "{$this->duration_count} " . Str::plural($this->duration_type, $this->duration_count);
    }
}
