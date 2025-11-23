<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Traits\APIResponse;

class OrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $path = $this->path();

        switch ($this->method()) {
            case 'POST':
                if (str_contains($path, 'orders/from-cart')) {
                    return [
                        'address' => 'nullable|string|max:255',
                    ];
                }

                if (str_contains($path, 'items/') && str_contains($path, '/order')) {
                    return [
                        'address' => 'nullable|string|max:255',
                        'quantity' => 'required|integer|min:1',
                    ];
                }

                // Default (fallback)
                return [
                    'address' => 'nullable|string|max:255',
                ];

            case 'PUT':
            case 'PATCH':
                return [
                    'status' => 'required|string|in:pending,processing,completed,cancelled',
                    'quantity' => 'integer|min:1',
                ];

            default:
                return [];
        }
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            APIResponse::errorResponse(
                'Validation failed', 
                422, 
                $validator->errors()->toArray()
            )
        );
    }
}
