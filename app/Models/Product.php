<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['cafe_id', 'category_id', 'name', 'description', 'price', 'discount_percentage', 'stock', 'sku', 'image_url', 'is_active', 'has_variants', 'variants'];

    protected $casts = [
        'has_variants' => 'boolean',
        'discount_percentage' => 'integer',
        'variants' => 'array',
    ];

    public function cafe()
    {
        return $this->belongsTo(Cafe::class);
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
