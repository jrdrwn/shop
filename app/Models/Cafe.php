<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cafe extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'address', 'phone', 'email', 'city', 'province',
        'description', 'owner_name', 'logo_url', 'is_active',
        'created_by', 'subscription_id',
        'tax_percentage', 'service_charge_percentage',
    ];

    protected $casts = [
        'tax_percentage' => 'integer',
        'service_charge_percentage' => 'integer',
        'is_active' => 'boolean',
    ];

    public function manager()
    {
        return $this->hasOne(CafeManager::class);
    }

    /**
     * All users (managers + cashiers) belonging to this cafe.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function paymentMethods()
    {
        return $this->hasMany(PaymentMethod::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
