<?php

namespace App\Models;

use App\Services\SubscriptionService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = ['toko_id', 'name', 'type', 'is_active'];

    protected static function booted()
    {
        static::updating(function ($paymentMethod) {
            if ($paymentMethod->isDirty('is_active') && $paymentMethod->is_active) {
                $toko = $paymentMethod->toko;
                if ($toko) {
                    $subscription = app(SubscriptionService::class)->subscriptionFor($toko);
                    if ($subscription && $subscription->plan?->value === 'free') {
                        $max = 2; // Limit for Free plan
                        $activeCount = $toko->paymentMethods()->where('is_active', true)->where('id', '!=', $paymentMethod->id)->count();
                        if ($activeCount >= $max) {
                            $oldest = $toko->paymentMethods()
                                ->where('is_active', true)
                                ->where('id', '!=', $paymentMethod->id)
                                ->orderBy('id', 'asc')
                                ->first();

                            if ($oldest) {
                                $oldest->update(['is_active' => false]);
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

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
