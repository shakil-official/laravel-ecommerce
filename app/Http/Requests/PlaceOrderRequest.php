<?php

namespace App\Http\Requests;

use App\Enums\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validation for the public "Order Now" form.
 */
class PlaceOrderRequest extends FormRequest
{
    /** Bangladeshi mobile number: 01XXXXXXXXX, +8801XXXXXXXXX or 8801XXXXXXXXX. */
    public const PHONE_REGEX = '/^(?:\+?88)?01[3-9]\d{8}$/';

    /** Anyone may submit; access is controlled by routes / middleware. */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'customer_name' => ['required', 'string', 'min:2', 'max:100'],
            'phone' => ['required', 'string', 'regex:'.self::PHONE_REGEX],
            'email' => ['nullable', 'email', 'max:150'],
            'address' => ['required', 'string', 'min:10', 'max:500'],
            'quantity' => ['required', 'integer', 'min:1', 'max:100'],
            'payment_method' => ['required', Rule::enum(PaymentMethod::class)],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }

    /** Normalise input (trim / format) before the rules run. */
    protected function prepareForValidation(): void
    {
        if (is_string($this->phone)) {
            $phone = preg_replace('/[\s-]+/', '', $this->phone);
            // Store one canonical format (01XXXXXXXXX) so search and masking behave.
            $phone = preg_replace('/^(?:\+?88)(01\d{9})$/', '$1', $phone);
            $this->merge(['phone' => $phone]);
        }
    }

    /** Send the visitor back to the order form, not the top of the page. */
    protected function getRedirectUrl(): string
    {
        return parent::getRedirectUrl().'#order-form';
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'phone.regex' => 'Please enter a valid Bangladeshi mobile number (e.g. 01712345678).',
            'address.min' => 'Please enter your full delivery address.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'customer_name' => 'name',
            'payment_method' => 'payment method',
        ];
    }
}
