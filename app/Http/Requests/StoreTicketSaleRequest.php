<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTicketSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        // use a policy or role check as needed; default to authenticated users
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'customer_name' => ['nullable', 'string', 'max:191'],
            'customer_phone' => ['nullable', 'string', 'max:20'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.ticket_type_id' => ['required', 'integer', 'exists:ticket_types,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'At least one ticket item is required.',
            'items.*.ticket_type_id.exists' => 'Selected ticket type does not exist.',
            'items.*.quantity.min' => 'Quantity must be at least 1.',
        ];
    }

    public function validatedData(): array
    {
        return $this->validated();
    }
}

