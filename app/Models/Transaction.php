<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = ['cafe_id', 'cashier_id', 'transaction_number', 'total_amount', 'discount_amount', 'tax_amount', 'paid_amount', 'change_amount', 'status', 'notes'];

    public function cafe()
    {
        return $this->belongsTo(Cafe::class);
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function items()
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function history()
    {
        return $this->hasMany(TransactionHistory::class);
    }
}
