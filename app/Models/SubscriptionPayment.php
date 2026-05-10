<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPayment extends Model
{
    use HasFactory;

    protected $table = 'subscription_payments';

    protected $fillable = [
        'cafe_id',
        'subscription_id',
        'order_id',
        'amount',
        'status',
        'payment_type',
        'transaction_id',
        'transaction_time',
        'settlement_time',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'metadata' => 'array',
        'transaction_time' => 'datetime',
        'settlement_time' => 'datetime',
    ];

    public function cafe()
    {
        return $this->belongsTo(Cafe::class);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isSuccess(): bool
    {
        return $this->status === 'success';
    }

    public function isFailed(): bool
    {
        return in_array($this->status, ['failed', 'expire', 'cancel'], true);
    }
}
