<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;

class WebhookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_id' => 'required|integer|exists:orders,id',
            'status'   => 'required|string|in:PAID,FAILED',
        ];
    }
}