<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $table = 'vehicle';
    protected $fillable = [
        'user_id',
        'type',
        'brand',
        'pelak',
        'color',
        'model',
    ];

    public function types()
    {
        return [
            0 => 'motor',
            1 => 'car',
        ];

    }




}
