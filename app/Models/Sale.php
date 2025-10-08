<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sale extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'client_id',
        'branch_id',
        'user_id',
        'folio',
        'total',
        'paid_out',
    ];

    public function details(): HasMany
    {
        return $this->hasMany(SaleDetail::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Customer::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Branch::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}