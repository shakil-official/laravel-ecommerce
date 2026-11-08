<?php

namespace App\Http\Requests;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation for the "Track your order" form.
 */
class TrackOrderRequest extends FormRequest
{
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
            // After normalisation a token is always 12 characters + 2 dashes.
            'token' => ['required', 'string', 'size:14', 'regex:/^[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}$/'],
        ];
    }

    /** Normalise input (trim / format) before the rules run. */
    protected function prepareForValidation(): void
    {
        if (is_string($this->token)) {
            $this->merge(['token' => Order::normalizeToken($this->token)]);
        }
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'token.required' => 'Please enter your tracking token.',
            'token.size' => 'The tracking token has 12 characters, like K7M2-9QXA-W4TP.',
            'token.regex' => 'The tracking token has 12 letters/numbers, like K7M2-9QXA-W4TP.',
        ];
    }
}
