<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NeighborhoodsAvailable extends Model
{

    protected $table = 'neighborhoods_availables';
    use HasFactory;

    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class)->with('user')->select(['*']);
    }


    public function neighborhood()
    {
        return $this->belongsTo(Neighborhood::class)->select(['id', 'name', 'code']);
    }
}
