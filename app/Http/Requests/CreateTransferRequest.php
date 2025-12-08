<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateTransferRequest extends FormRequest
{
    public function authorize() {
        return true;
    }

    public function rules() {
        return [
            'new_branch_id' => 'required|integer|exists:branches,id',
            
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|integer|exists:products,id',
        ];
    }
}