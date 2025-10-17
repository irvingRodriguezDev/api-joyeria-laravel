<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shop extends Model
{
    use SoftDeletes;
    protected $fillable = ['user_id', 'name', 'state', 'municipality'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function branches()
    {
        return $this->hasMany(Branch::class);
    }
}