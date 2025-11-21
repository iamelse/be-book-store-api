<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Traits\APIResponse;

class CartItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        switch ($this->method()) {
            case 'POST':
                return [
                    'item_id'  => 'required|integer|exists:items,id',
                    'quantity' => 'required|integer|min:1',
                ];

            case 'PUT':
            case 'PATCH':
                return [
                    'quantity' => 'required|integer|min:1',
                ];

            default:
                return [];
        }
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            APIResponse::errorResponse('Validation failed', 422, $validator->errors()->toArray())
        );
    }
}