<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreBookingRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'customer_name' => 'required|string|max:100',
            'customer_phone' => 'required|string|max:20',
            'checkin' => 'required|date|after_or_equal:today',
            'checkout' => 'required|date|after:checkin',
            'night_count' => 'required|integer|min:1',
            'room_count' => 'nullable|integer|min:1',
            'total_amount' => 'required|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'dp_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
            'booking_units' => 'required|array|min:1',
            'booking_units.*.product_id' => 'required|exists:products,id',
            'booking_units.*.unit_code' => 'nullable|string|max:50',
            'booking_units.*.qty' => 'required|integer|min:1',
            'booking_units.*.rate' => 'required|numeric|min:0',
            'booking_units.*.discount' => 'nullable|numeric|min:0',
            'booking_units.*.subtotal' => 'required|numeric|min:0',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'checkin.after_or_equal' => 'Check-in date must be today or later',
            'checkout.after' => 'Check-out date must be after check-in date',
            'booking_units.required' => 'At least one booking unit is required',
            'booking_units.*.product_id.exists' => 'Invalid product selected',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            // Validate checkout > checkin
            $checkin = $this->input('checkin');
            $checkout = $this->input('checkout');
            
            if ($checkin && $checkout && strtotime($checkout) <= strtotime($checkin)) {
                $validator->errors()->add('checkout', 'Check-out must be after check-in date');
            }

            // Validate total_amount matches sum of subtotals
            $units = $this->input('booking_units', []);
            $calculatedTotal = array_sum(array_column($units, 'subtotal'));
            $totalAmount = $this->input('total_amount', 0);
            $discountAmount = $this->input('discount_amount', 0);

            if (abs(($calculatedTotal - $discountAmount) - $totalAmount) > 0.01) {
                $validator->errors()->add('total_amount', 'Total amount does not match calculated subtotal');
            }
        });
    }
}
