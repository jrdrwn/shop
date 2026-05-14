<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['toko_id', 'user_id', 'model_type', 'model_id', 'action', 'changes', 'ip_address', 'user_agent'])]
class ActivityLog extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected $casts = [
        'changes' => 'array',
    ];

    public function toko(): BelongsTo
    {
        return $this->belongsTo(Toko::class, 'toko_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
