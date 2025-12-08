<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transfer extends Model
{
    protected $fillable = [
        'product_id',
        'last_branch_id',
        'new_branch_id',
        'status',
        'user_origin_id',
        'user_destination_id'
    ];

    public function originBranch() {
        return $this->belongsTo(Branch::class, 'last_branch_id');
    }

    public function destinationBranch() {
        return $this->belongsTo(Branch::class, 'new_branch_id');
    }

    public function product() {
        return $this->belongsTo(Product::class);
    }

    public function originUser() {
        return $this->belongsTo(User::class, 'user_origin_id');
    }

    public function destinationUser() {
        return $this->belongsTo(User::class, 'user_destination_id');
    }
}