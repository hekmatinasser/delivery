<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trip extends Model
{
    use HasFactory;
    protected $guarded = [];


    public function store()
    {
        return $this->belongsTo(Store::class)->with('category', 'user')->select(['*']);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class)->with('user')->select(['*']);
    }
    public function origin()
    {
        return $this->belongsTo(Neighborhood::class, 'origin')->select(['*']);
    }
    public function destination()
    {
        return $this->belongsTo(Neighborhood::class, 'destination')->select(['*']);
    }
}
