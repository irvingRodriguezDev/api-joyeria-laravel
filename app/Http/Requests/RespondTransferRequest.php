<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RespondTransferRequest extends FormRequest
{
    public function authorize() {
        return true;
    }

    public function rules() {
        return [
            'transfer_ids' => 'required|array|min:1',
            'transfer_ids.*' => 'required|integer|exists:transfers,id',

            'action' => 'required|in:accept,reject,cancel',
        ];
    }
}