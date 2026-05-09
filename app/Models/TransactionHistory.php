<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionHistory extends Model
{
    use HasFactory;

    protected $table = 'transaction_history';

    protected $fillable = ['cafe_id', 'transaction_id', 'action', 'performed_by', 'description'];

    public function cafe()
    {
        return $this->belongsTo(Cafe::class);
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
