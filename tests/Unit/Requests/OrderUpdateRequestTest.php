<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\OrderUpdateRequest;
use Tests\TestCase;

class OrderUpdateRequestTest extends TestCase
{
    use RequestTestHelper;

    public function test_authorize_is_true(): void
    {
        $this->assertTrue((new OrderUpdateRequest)->authorize());
    }

    public function test_every_status_combination_is_accepted(): void
    {
        foreach (['pending', 'confirmed', 'packaging', 'shipped', 'delivered', 'cancelled'] as $status) {
            foreach (['unpaid', 'paid', 'failed', 'refunded'] as $payment) {
                if ($status === 'cancelled' && $payment === 'paid') {
                    continue; // forbidden combination, see the test below
                }

                $v = $this->validate(OrderUpdateRequest::class, ['status' => $status, 'payment_status' => $payment], 'PUT');
                $this->assertTrue($v->passes(), "{$status}/{$payment}");
            }
        }
    }

    public function test_cancelled_order_cannot_be_paid(): void
    {
        $v = $this->validate(OrderUpdateRequest::class, ['status' => 'cancelled', 'payment_status' => 'paid'], 'PUT');

        $this->assertTrue($v->errors()->has('payment_status'));
        $this->assertStringContainsString('refunded', $v->errors()->first('payment_status'));
    }

    public function test_both_fields_are_required(): void
    {
        $errors = $this->validate(OrderUpdateRequest::class, [], 'PUT')->errors();

        $this->assertTrue($errors->has('status'));
        $this->assertTrue($errors->has('payment_status'));
    }

    public function test_unknown_values_are_rejected(): void
    {
        $errors = $this->validate(OrderUpdateRequest::class, ['status' => 'teleported', 'payment_status' => 'maybe'], 'PUT')->errors();

        $this->assertTrue($errors->has('status'));
        $this->assertTrue($errors->has('payment_status'));
    }
}
