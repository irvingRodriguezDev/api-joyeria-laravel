<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'sale_id',
        'amount',
        'payment_method',
        'reference',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }
}