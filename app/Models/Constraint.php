<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Constraint extends Model
{
    use HasFactory;

    protected $casts = [
        'quarantined_users' => 'array',
    ];
}
