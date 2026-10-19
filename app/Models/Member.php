<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;

class Member extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'member_code',
        'first_name',
        'last_name',
        'gender',
        'date_of_birth',
        'email',
        'phone',
        'emergency_contact_name',
        'emergency_contact_phone',
        'blood_type',
        'address',
        'city',
        'postal_code',
        'medical_notes',
        'fitness_goals',
        'barcode_value',
        'rfid_card_number',
        'avatar',
        'status',
        'joined_date',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'joined_date' => 'date',
            'fitness_goals' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function healthMetrics(): HasMany
    {
        return $this->hasMany(MemberHealthMetric::class)->orderBy('recorded_date', 'desc');
    }

    public function latestMetric(): HasOne
    {
        return $this->hasOne(MemberHealthMetric::class)->latestOfMany('recorded_date');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(MemberSubscription::class)->orderBy('created_at', 'desc');
    }

    public function activeSubscription(): HasOne
    {
        return $this->hasOne(MemberSubscription::class)
            ->where('status', 'active')
            ->whereDate('end_date', '>=', now()->toDateString())
            ->latestOfMany();
    }

    public function currentSubscription(): HasOne
    {
        return $this->activeSubscription();
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class)->orderBy('check_in_time', 'desc');
    }

    public function trainerAssignments(): HasMany
    {
        return $this->hasMany(TrainerMemberAssignment::class);
    }

    public function classBookings(): HasMany
    {
        return $this->hasMany(ClassBooking::class);
    }

    public function locker(): HasOne
    {
        return $this->hasOne(Locker::class, 'assigned_member_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class)->orderBy('created_at', 'desc');
    }

    public function workoutPlans(): HasMany
    {
        return $this->hasMany(WorkoutPlan::class);
    }

    public function dietPlans(): HasMany
    {
        return $this->hasMany(DietPlan::class);
    }

    // Accessors & Helpers
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getAgeAttribute(): ?int
    {
        return $this->date_of_birth ? Carbon::parse($this->date_of_birth)->age : null;
    }

    public function hasActiveSubscription(): bool
    {
        return $this->activeSubscription !== null;
    }

    public function isEligibleForCheckIn(): array
    {
        if ($this->status === 'suspended') {
            return ['allowed' => false, 'message' => 'Member is currently suspended due to policy violation.'];
        }

        if ($this->status === 'frozen') {
            return ['allowed' => false, 'message' => 'Membership is currently frozen / paused.'];
        }

        $activeSub = $this->activeSubscription;
        if (!$activeSub) {
            // Check grace period from config
            $graceDays = config('gym.allow_grace_period_days', 3);
            $lastSub = $this->subscriptions()->orderBy('end_date', 'desc')->first();
            
            if ($lastSub && Carbon::parse($lastSub->end_date)->addDays($graceDays)->isFuture()) {
                $daysRemaining = Carbon::now()->diffInDays(Carbon::parse($lastSub->end_date)->addDays($graceDays), false);
                return [
                    'allowed' => true,
                    'warning' => true,
                    'message' => "Membership expired on {$lastSub->end_date->format('M d, Y')}! In grace period ({$daysRemaining} days left). Please renew.",
                ];
            }

            return ['allowed' => false, 'message' => 'No active membership subscription found.'];
        }

        return ['allowed' => true, 'warning' => false, 'message' => 'Membership is valid and active.'];
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeSearch($query, ?string $term)
    {
        if (!$term) {
            return $query;
        }

        $termLower = '%' . mb_strtolower(trim($term)) . '%';

        return $query->where(function ($q) use ($termLower) {
            $q->whereRaw('LOWER(first_name) LIKE ?', [$termLower])
              ->orWhereRaw('LOWER(last_name) LIKE ?', [$termLower])
              ->orWhereRaw('LOWER(member_code) LIKE ?', [$termLower])
              ->orWhereRaw('LOWER(email) LIKE ?', [$termLower])
              ->orWhereRaw('LOWER(phone) LIKE ?', [$termLower])
              ->orWhereRaw('LOWER(barcode_value) LIKE ?', [$termLower])
              ->orWhereRaw('LOWER(rfid_card_number) LIKE ?', [$termLower]);
        });
    }

    public static function generateMemberCode(): string
    {
        $year = date('Y');
        $prefix = "GYM-{$year}-";

        $latest = static::where('member_code', 'like', $prefix . '%')
            ->orderByRaw('LENGTH(member_code) DESC, member_code DESC')
            ->value('member_code');

        $nextSeq = 1001;
        if ($latest && preg_match('/-(\d+)$/', $latest, $matches)) {
            $nextSeq = (int) $matches[1] + 1;
        }

        do {
            $candidate = sprintf('%s%04d', $prefix, $nextSeq);
            $nextSeq++;
        } while (static::where('member_code', $candidate)->exists());

        return $candidate;
    }
}
