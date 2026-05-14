<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashFlow extends Model
{
    protected $fillable = ['toko_id', 'type', 'category', 'amount', 'description', 'reference_id', 'reference_type', 'created_by'];

    const UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function toko(): BelongsTo
    {
        return $this->belongsTo(Toko::class, 'toko_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
