<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TheHistoryOfInterNeighborhoodFare extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }


    //Inter Neighborhood Fare
    public function inf(): BelongsTo
    {
        return $this->belongsTo(InterNeighborhoodFare::class, 'INF_id')->with('origin', 'destination');
    }
}
