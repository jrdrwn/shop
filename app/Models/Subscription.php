<?php

namespace App\Models;

use App\Enums\SubscriptionPlan;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'plan', 'price', 'duration_months', 'features', 'limits', 'is_active'];

    protected $casts = [
        'plan' => SubscriptionPlan::class,
        'limits' => 'array',
        'is_active' => 'boolean',
        'duration_months' => 'integer',
    ];

    public function tokos()
    {
        return $this->hasMany(Toko::class);
    }

    /**
     * Read legacy textarea values and store marketing features as a JSON list.
     *
     * @return Attribute<array<int, string>|null, array<int, string>|string|null>
     */
    protected function features(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value): ?array => $this->normalizeFeatures($value),
            set: fn (mixed $value): ?string => $value === null
                ? null
                : json_encode($this->normalizeFeatures($value), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
        );
    }

    /**
     * @return array<int, string>|null
     */
    private function normalizeFeatures(mixed $value): ?array
    {
        if ($value === null) {
            return null;
        }

        for ($depth = 0; $depth < 3 && is_string($value); $depth++) {
            $decoded = json_decode($value, true);

            if (json_last_error() !== JSON_ERROR_NONE || (! is_array($decoded) && ! is_string($decoded))) {
                break;
            }

            $value = $decoded;
        }

        if (is_string($value)) {
            $value = preg_split('/\r\n|\r|\n/', $value);
        }

        if (! is_array($value)) {
            return [];
        }

        return array_values(array_filter(
            array_map(fn (mixed $feature): string => is_string($feature) ? trim($feature) : '', $value),
            fn (string $feature): bool => $feature !== '',
        ));
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
