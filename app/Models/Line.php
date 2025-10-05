<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Line extends Model
{
    use HasFactory;

    protected $table = 'lines';

    protected $fillable = [
        'name',
        'price_purchase',
        'price',
        'percent_discount',
        'shop_id',
    ];

    protected $casts = [
        'price_purchase' => 'decimal:2',
        'price' => 'decimal:2',
        'percent_discount' => 'decimal:2',
    ];
}