<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\BusinessRule;
class Category extends Model
{

    protected $fillable = ['name', 'type_product_id', 'business_rule_id', 'shop_id'];

    public function typeProduct()
    {
        return $this->belongsTo(TypeProduct::class);
    }
    
    public function businessRule()
    {
        return $this->belongsTo(BusinessRule::class);
    }
}