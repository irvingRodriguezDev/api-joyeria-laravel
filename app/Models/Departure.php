<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Departure extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'auth',
        'recibe',
        'description',
        'branch_id',
        'user_id',
    ];

    // Relaciones
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function details()
    {
        return $this->hasMany(DepartureDetail::class);
    }
}