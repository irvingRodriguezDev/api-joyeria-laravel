<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'clave',
        'description',
        'category_id',
        'line_id',
        'branch_id',
        'shop_id',
        'status_id',
        'weight',
        'observations',
        'price_purchase',
        'price',
        'price_with_discount',
    ];

    // Relaciones
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function line()
    {
        return $this->belongsTo(Line::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    public function saleDetails(){
        return $this->hasMany(SaleDetail::class);
    }
}