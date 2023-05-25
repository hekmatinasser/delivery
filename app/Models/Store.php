<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use HasFactory;

    protected $table = 'store';
    protected $fillable = [
        'user_id',
        'category_id',
        'name',
        'address',
        'postCode',
        'lot',
        'lang',
        'areaType',
        'phone',
    ];

    public function areaTypes()
    {
        return [
            0 => 'اجاری',
            1 => 'مالکیت',
        ];
    }

    public function category()
    {
        return $this->belongsTo(StoreCategory::class , 'category_id');

    }




}
