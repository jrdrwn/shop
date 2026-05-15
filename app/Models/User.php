<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Services\SubscriptionService;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

#[Fillable(['name', 'email', 'password', 'role', 'phone', 'toko_id', 'is_active'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    protected static function booted()
    {
        static::creating(function ($user) {
            if ($user->role === 'cashier' && $user->is_active) {
                $toko = $user->toko;
                if ($toko) {
                    $subscription = app(SubscriptionService::class)->subscriptionFor($toko);
                    if ($subscription) {
                        $max = $subscription->getLimit('max_staff');
                        if ($max !== null) {
                            $activeCount = $toko->users()->where('role', 'cashier')->where('is_active', true)->count();
                            if ($activeCount >= $max) {
                                $oldest = $toko->users()
                                    ->where('role', 'cashier')
                                    ->where('is_active', true)
                                    ->orderBy('id', 'asc')
                                    ->first();

                                if ($oldest) {
                                    $oldest->update(['is_active' => false]);
                                }
                            }
                        }
                    }
                }
            }
        });

        static::updating(function ($user) {
            if ($user->role === 'cashier' && $user->isDirty('is_active') && $user->is_active) {
                $toko = $user->toko;
                if ($toko) {
                    $subscription = app(SubscriptionService::class)->subscriptionFor($toko);
                    if ($subscription) {
                        $max = $subscription->getLimit('max_staff');
                        if ($max !== null) {
                            $activeCount = $toko->users()->where('role', 'cashier')->where('is_active', true)->where('id', '!=', $user->id)->count();
                            if ($activeCount >= $max) {
                                $oldest = $toko->users()
                                    ->where('role', 'cashier')
                                    ->where('is_active', true)
                                    ->where('id', '!=', $user->id)
                                    ->orderBy('id', 'asc')
                                    ->first();

                                if ($oldest) {
                                    $oldest->update(['is_active' => false]);
                                }
                            }
                        }
                    }
                }
            }
        });
    }

    public function toko()
    {
        return $this->belongsTo(Toko::class, 'toko_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'cashier_id');
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return match ($panel->getId()) {
            'admin' => in_array($this->role, [UserRole::SuperAdmin->value, 'admin'], true),
            'cashier' => in_array($this->role, [UserRole::Cashier->value, 'cashier'], true),
            'owner' => in_array($this->role, [UserRole::Owner->value, 'owner'], true),
            'warehouse' => in_array($this->role, [UserRole::Warehouse->value, 'gudang'], true),
            default => false,
        };
    }
}
