<?php

namespace Tests\Unit\Enums;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use PHPUnit\Framework\TestCase;

class EnumsTest extends TestCase
{
    public function test_order_status_values_cover_the_workflow(): void
    {
        $this->assertSame(
            ['pending', 'confirmed', 'packaging', 'shipped', 'delivered', 'cancelled'],
            OrderStatus::values()
        );
    }

    public function test_order_status_label_and_colour_exist_for_every_case(): void
    {
        foreach (OrderStatus::cases() as $status) {
            $this->assertSame(ucfirst($status->value), $status->label());
            $this->assertNotSame('', $status->color());
        }
    }

    public function test_order_status_is_final_only_for_delivered_and_cancelled(): void
    {
        $this->assertTrue(OrderStatus::Delivered->isFinal());
        $this->assertTrue(OrderStatus::Cancelled->isFinal());
        $this->assertFalse(OrderStatus::Pending->isFinal());
        $this->assertFalse(OrderStatus::Packaging->isFinal());
        $this->assertFalse(OrderStatus::Shipped->isFinal());
    }

    public function test_payment_status_values_label_and_colour(): void
    {
        $this->assertSame(['unpaid', 'paid', 'failed', 'refunded'], PaymentStatus::values());
        $this->assertSame('Paid', PaymentStatus::Paid->label());
        $this->assertSame('success', PaymentStatus::Paid->color());
        $this->assertSame('danger', PaymentStatus::Failed->color());
        $this->assertSame('warning', PaymentStatus::Unpaid->color());
        $this->assertSame('info', PaymentStatus::Refunded->color());
    }

    public function test_payment_method_values_and_labels(): void
    {
        $this->assertSame(['cod', 'bkash', 'nagad', 'bank_transfer'], PaymentMethod::values());
        $this->assertSame('Cash on delivery', PaymentMethod::Cod->label());
        $this->assertSame('bKash', PaymentMethod::Bkash->label());
        $this->assertSame('Nagad', PaymentMethod::Nagad->label());
        $this->assertSame('Bank transfer', PaymentMethod::BankTransfer->label());
    }

    public function test_tracking_steps_exclude_cancelled_and_are_ordered(): void
    {
        $this->assertSame(
            [OrderStatus::Pending, OrderStatus::Confirmed, OrderStatus::Packaging, OrderStatus::Shipped, OrderStatus::Delivered],
            OrderStatus::trackingSteps()
        );
    }

    public function test_step_position_and_cancelled_has_none(): void
    {
        $this->assertSame(0, OrderStatus::Pending->step());
        $this->assertSame(2, OrderStatus::Packaging->step());
        $this->assertSame(4, OrderStatus::Delivered->step());
        $this->assertNull(OrderStatus::Cancelled->step());
    }

    public function test_icon_and_description_exist_for_every_status(): void
    {
        foreach (OrderStatus::cases() as $status) {
            $this->assertStringStartsWith('bi-', $status->icon());
            $this->assertNotSame('', $status->description());
        }
    }
}
