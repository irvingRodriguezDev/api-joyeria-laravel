<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{
    protected $fillable = ['user_id', 'name', 'state', 'municipality'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}