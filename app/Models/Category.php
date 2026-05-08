<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['cafe_id','name','description','image_url','display_order','is_active'];

    public function cafe()
    {
        return $this->belongsTo(Cafe::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
