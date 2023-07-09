<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoresBlocked extends Model
{
    use HasFactory;


    public function store()
    {
        return $this->belongsTo(Store::class)->select(['*']);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class)->select(['*']);
    }
}
