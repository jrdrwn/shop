<?php

namespace App\Models;

use App\Services\SubscriptionService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['toko_id', 'category_id', 'name', 'description', 'price', 'discount_percentage', 'stock', 'sku', 'image_url', 'is_active', 'has_variants', 'variants'];

    protected $casts = [
        'has_variants' => 'boolean',
        'discount_percentage' => 'integer',
        'variants' => 'array',
    ];

    protected static function booted()
    {
        static::creating(function ($product) {
            if ($product->is_active) {
                $toko = Toko::find($product->toko_id);
                if ($toko) {
                    $subscription = app(SubscriptionService::class)->subscriptionFor($toko);
                    if ($subscription) {
                        $max = $subscription->getLimit('max_products');
                        if ($max !== null) {
                            $activeCount = $toko->products()->where('is_active', true)->count();
                            if ($activeCount >= $max) {
                                $oldest = $toko->products()
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

        static::updating(function ($product) {
            if ($product->isDirty('is_active') && $product->is_active) {
                $toko = Toko::find($product->toko_id);
                if ($toko) {
                    $subscription = app(SubscriptionService::class)->subscriptionFor($toko);
                    if ($subscription) {
                        $max = $subscription->getLimit('max_products');
                        if ($max !== null) {
                            $activeCount = $toko->products()->where('is_active', true)->where('id', '!=', $product->id)->count();
                            if ($activeCount >= $max) {
                                $oldest = $toko->products()
                                    ->where('is_active', true)
                                    ->where('id', '!=', $product->id)
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

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function transactionItems()
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function inventoryLogs()
    {
        return $this->hasMany(InventoryLog::class);
    }
}
