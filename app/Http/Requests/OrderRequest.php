<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
        switch ($this->method()) {
            case 'POST':
                // Membuat pesanan baru
                return [
                    'address' => 'required|string|max:255',
                ];

            case 'PUT':
            case 'PATCH':
                // Update status pesanan (admin)
                return [
                    'status' => 'required|string|in:pending,processing,completed,cancelled',
                ];

            default:
                return [];
        }
    }
}
