<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    use HasFactory,  SoftDeletes;


    protected $fillable = [
        'branch_name',
        'legal_representative',
        'email',
        'rfc',
        'phone',
        'address',
        'shop_id',
        'state_id',
        'municipality_id',
    ];
    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function municipality()
    {
        return $this->belongsTo(Municipality::class);
    }
}