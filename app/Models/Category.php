<?php

namespace App\Models;

use App\Services\SubscriptionService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['toko_id', 'name', 'description', 'image_url', 'display_order', 'is_active'];

    protected static function booted()
    {
        static::creating(function ($category) {
            if ($category->is_active) {
                $toko = Toko::find($category->toko_id);
                if ($toko) {
                    $subscription = app(SubscriptionService::class)->subscriptionFor($toko);
                    if ($subscription) {
                        $max = $subscription->getLimit('max_categories');
                        if ($max !== null) {
                            $activeCount = $toko->categories()->where('is_active', true)->count();
                            if ($activeCount >= $max) {
                                $oldest = $toko->categories()
                                    ->where('is_active', true)
                                    ->orderBy('id', 'asc')
                                    ->first();

                                if ($oldest) {
                                    $oldest->update(['is_active' => false]);
                                    $oldest->products()->update(['is_active' => false]);
                                }
                            }
                        }
                    }
                }
            }
        });

        static::updating(function ($category) {
            if ($category->isDirty('is_active') && $category->is_active) {
                $toko = Toko::find($category->toko_id);
                if ($toko) {
                    $subscription = app(SubscriptionService::class)->subscriptionFor($toko);
                    if ($subscription) {
                        $max = $subscription->getLimit('max_categories');
                        if ($max !== null) {
                            $activeCount = $toko->categories()->where('is_active', true)->where('id', '!=', $category->id)->count();
                            if ($activeCount >= $max) {
                                $oldest = $toko->categories()
                                    ->where('is_active', true)
                                    ->where('id', '!=', $category->id)
                                    ->orderBy('id', 'asc')
                                    ->first();

                                if ($oldest) {
                                    $oldest->update(['is_active' => false]);
                                    $oldest->products()->update(['is_active' => false]);
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

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
