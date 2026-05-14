<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionHistory extends Model
{
    use HasFactory;

    protected $table = 'transaction_history';

    protected $fillable = ['toko_id', 'transaction_id', 'action', 'performed_by', 'description'];

    public function toko()
    {
        return $this->belongsTo(Toko::class, 'toko_id');
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function performedBy()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
