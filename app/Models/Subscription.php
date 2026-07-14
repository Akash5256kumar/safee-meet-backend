<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'plan', 'price', 'billing_cycle', 'status',
        'trial_days', 'started_at', 'renews_at', 'cancelled_at',
        'stripe_customer_id', 'stripe_subscription_id',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'renews_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'price' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['trial', 'active']);
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    public function scopeTrial($query)
    {
        return $query->where('status', 'trial');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'trial' => 'Trial',
            'active' => 'Active',
            'expired' => 'Expired',
            'cancelled' => 'Cancelled',
            default => ucfirst((string) $this->status),
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'active' => '#4ade80',
            'trial' => '#facc15',
            'expired', 'cancelled' => '#f87171',
            default => '#9ca3af',
        };
    }

    public function getPlanLabelAttribute(): string
    {
        return match ($this->plan) {
            'free_trial' => 'Free Trial',
            'basic' => 'Basic',
            'premium' => 'Premium',
            'professional' => 'Professional',
            default => ucfirst(str_replace('_', ' ', (string) $this->plan)),
        };
    }
}
