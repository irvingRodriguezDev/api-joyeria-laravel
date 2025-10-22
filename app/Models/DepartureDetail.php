<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class DepartureDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'departure_id',
        'product_id',
    ];

    // Relaciones
    public function departure()
    {
        return $this->belongsTo(Departure::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}