<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleConstraint extends Model
{
    use HasFactory;

    protected $casts = [
        'quarantined_neighborhood' => 'array',
    ];
}
