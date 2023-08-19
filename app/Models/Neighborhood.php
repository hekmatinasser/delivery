<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Neighborhood extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->select(['name']);
    }

    public function fee()
    {
        return $this->hasMany(InterNeighborhoodFare::class, 'origin', 'id');
    }

    public function feeBack()
    {
        return $this->hasMany(InterNeighborhoodFare::class, 'destination', 'id');
    }
}
