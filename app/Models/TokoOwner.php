<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TokoOwner extends Model
{
    use HasFactory;

    protected $table = 'toko_owners';

    protected $fillable = [
        'toko_id', 'owner_id', 'assigned_at', 'assigned_by',
    ];

    public function toko()
    {
        return $this->belongsTo(Toko::class, 'toko_id');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
