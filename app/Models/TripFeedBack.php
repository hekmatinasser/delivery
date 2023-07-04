<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TripFeedBack extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function trip()
    {
        return $this->belongsTo(Trip::class)->with(['vehicle', 'store', 'origin', 'destination']);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
