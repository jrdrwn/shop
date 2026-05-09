<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyReport extends Model
{
    use HasFactory;

    protected $fillable = ['cafe_id', 'report_date', 'total_transactions', 'total_sales', 'total_discount', 'total_tax', 'total_cash', 'total_debit', 'total_qris', 'opening_balance', 'closing_balance', 'created_by'];

    public function cafe()
    {
        return $this->belongsTo(Cafe::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
