<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{

    protected $fillable = ['name', 'type_product_id'];

    public function typeProduct()
    {
        return $this->belongsTo(TypeProduct::class);
    }
}