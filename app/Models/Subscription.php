<?php

namespace App\Models;

use App\Enums\SubscriptionPlan;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'plan', 'price', 'duration_months', 'features', 'limits', 'is_active'];

    protected $casts = [
        'plan' => SubscriptionPlan::class,
        'features' => 'array',
        'limits' => 'array',
        'is_active' => 'boolean',
    ];

    public function tokos()
    {
        return $this->hasMany(Toko::class);
    }

    /**
     * Resolve the effective limits: plan defaults merged with any custom overrides stored in `limits`.
     *
     * @return array<string, mixed>
     */
    public function effectiveLimits(): array
    {
        $defaults = $this->plan?->defaultLimits() ?? SubscriptionPlan::Free->defaultLimits();
        $overrides = $this->limits ?? [];

        // Merge overrides directly — null in overrides means "explicitly set to unlimited"
        return array_merge($defaults, $overrides);
    }

    /**
     * Get a specific numeric limit. Returns null when unlimited.
     */
    public function getLimit(string $key): ?int
    {
        $value = $this->effectiveLimits()[$key] ?? null;

        // null or empty string means unlimited
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    /**
     * Check if a boolean feature is enabled for this subscription.
     */
    public function hasFeature(string $key): bool
    {
        return (bool) ($this->effectiveLimits()[$key] ?? false);
    }
}
