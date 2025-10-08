<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaleStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // ajusta según tu política de auth
    }

    public function rules(): array
    {
        return [
            'client_id'   => 'required|integer|exists:customers,id',
            'branch_id'   => 'required|integer|exists:branches,id',
            'user_id'     => 'required|integer|exists:users,id',
            'total'       => 'required|numeric|min:0',
            'paid_out'    => 'nullable|numeric|min:0',
            'productsList'=> 'required|array|min:1',
            'productsList.*.product_id'   => 'required|integer|exists:products,id',
            'productsList.*.final_price'  => 'required|numeric',
            'productsList.*.price_purchase'=> 'required|numeric',
            'productsList.*.quantity'     => 'nullable|integer|min:1',
            'payments'    => 'nullable|array',
            'payments.*.amount' => 'required_with:payments|numeric|min:0.01',
            'payments.*.payment_method' => 'nullable|string',
            'payments.*.reference' => 'nullable|string',
        ];
    }
}