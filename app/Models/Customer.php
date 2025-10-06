<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'lastname',
        'phone',
        'branch_id',
        'shop_id',
        'positive_balance',
    ];

    // Relaciones
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }
}