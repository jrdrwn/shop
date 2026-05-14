<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyReport extends Model
{
    use HasFactory;

    protected $fillable = ['toko_id', 'report_date', 'total_transactions', 'total_sales', 'total_discount', 'total_tax', 'total_cash', 'total_debit', 'total_qris', 'opening_balance', 'closing_balance', 'created_by'];

    public function toko()
    {
        return $this->belongsTo(Toko::class, 'toko_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
