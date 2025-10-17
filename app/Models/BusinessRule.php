<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Category;
use Illuminate\Database\Eloquent\SoftDeletes;

class BusinessRule extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['operator', 'multiplicator', 'percent_discount'];

    // Relación inversa (opcional) con Category
    public function categories()
    {
        return $this->hasMany(Category::class);
    }
}