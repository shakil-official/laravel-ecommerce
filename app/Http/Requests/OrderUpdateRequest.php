<?php

namespace App\Http\Requests;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

/**
 * Validation for changing an order's status / payment status in the admin.
 */
class OrderUpdateRequest extends FormRequest
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
            'status' => ['required', Rule::enum(OrderStatus::class)],
            'payment_status' => ['required', Rule::enum(PaymentStatus::class)],
        ];
    }

    /**
     * Business rule: a cancelled order cannot stay "paid" - the money must be
     * marked refunded (or the payment unpaid / failed).
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            if ($this->input('status') === OrderStatus::Cancelled->value
                && $this->input('payment_status') === PaymentStatus::Paid->value) {
                $v->errors()->add('payment_status', 'A cancelled order cannot be marked as paid. Mark it as refunded instead.');
            }
        });
    }
}
