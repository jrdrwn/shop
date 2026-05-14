<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryLog extends Model
{
    use HasFactory;

    protected $fillable = ['toko_id', 'product_id', 'action', 'quantity_change', 'quantity_before', 'quantity_after', 'reference_id', 'reference_type', 'notes', 'created_by'];

    public function toko()
    {
        return $this->belongsTo(Toko::class, 'toko_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
