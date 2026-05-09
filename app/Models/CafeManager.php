<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CafeManager extends Model
{
    use HasFactory;

    protected $table = 'cafe_managers';

    protected $fillable = [
        'cafe_id', 'manager_id', 'assigned_at', 'assigned_by',
    ];

    public function cafe()
    {
        return $this->belongsTo(Cafe::class);
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
