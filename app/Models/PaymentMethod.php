<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = ['cafe_id', 'name', 'type', 'is_active'];

    public function cafe()
    {
        return $this->belongsTo(Cafe::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
