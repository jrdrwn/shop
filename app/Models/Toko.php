<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Toko extends Model
{
    use HasFactory;

    protected $table = 'tokos';

    protected $fillable = [
        'name', 'address', 'phone', 'email', 'city', 'province',
        'description', 'owner_name', 'logo_url', 'is_active',
        'created_by', 'subscription_id',
        'tax_percentage', 'service_charge_percentage',
        'qris_type', 'midtrans_client_key', 'midtrans_server_key',
        'midtrans_merchant_id', 'midtrans_is_production',
    ];

    protected $casts = [
        'tax_percentage' => 'integer',
        'service_charge_percentage' => 'integer',
        'is_active' => 'boolean',
        'midtrans_is_production' => 'boolean',
    ];

    protected static function booted()
    {
        static::created(function ($toko) {
            $toko->paymentMethods()->createMany([
                ['name' => 'Tunai', 'type' => 'cash', 'is_active' => true],
                ['name' => 'QRIS', 'type' => 'qris', 'is_active' => true],
            ]);
        });
    }

    public function owner()
    {
        return $this->hasOne(User::class, 'toko_id')->where('role', 'owner');
    }

    /**
     * All users (owners + cashiers) belonging to this toko.
     */
    public function users()
    {
        return $this->hasMany(User::class, 'toko_id');
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function categories()
    {
        return $this->hasMany(Category::class, 'toko_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'toko_id');
    }

    public function paymentMethods()
    {
        return $this->hasMany(PaymentMethod::class, 'toko_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'toko_id');
    }

    public function suppliers()
    {
        return $this->hasMany(Supplier::class, 'toko_id');
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class, 'toko_id');
    }

    public function cashFlows()
    {
        return $this->hasMany(CashFlow::class, 'toko_id');
    }
}
