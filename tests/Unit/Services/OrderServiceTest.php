<?php

namespace Tests\Unit\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Product;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderServiceTest extends TestCase
{
    use RefreshDatabase;

    private function data(array $override = []): array
    {
        return array_merge([
            'customer_name' => 'Rahim Uddin',
            'phone' => '01712345678',
            'email' => 'rahim@example.com',
            'address' => 'House 12, Road 5, Dhanmondi, Dhaka',
            'note' => 'Call before delivery',
            'quantity' => 3,
            'payment_method' => 'bkash',
        ], $override);
    }

    public function test_place_creates_pending_unpaid_order_with_snapshot_and_total(): void
    {
        $product = Product::factory()->create(['name' => 'Nice Phone', 'old_price' => 1500, 'new_price' => 1250.50]);

        $order = (new OrderService)->place($product, $this->data());

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'product_id' => $product->id]);
        $this->assertSame('Nice Phone', $order->product_name);
        $this->assertSame('1250.50', $order->unit_price);
        $this->assertSame(3, $order->quantity);
        $this->assertSame('3751.50', $order->total);
        $this->assertSame(OrderStatus::Pending, $order->status);
        $this->assertSame(PaymentStatus::Unpaid, $order->payment_status);
        $this->assertSame(PaymentMethod::Bkash, $order->payment_method);
        $this->assertNotEmpty($order->order_number);
    }

    public function test_place_handles_missing_optional_fields(): void
    {
        $product = Product::factory()->create();

        $order = (new OrderService)->place($product, $this->data(['email' => null, 'note' => null]));

        $this->assertNull($order->email);
        $this->assertNull($order->note);
    }

    public function test_later_price_change_does_not_alter_existing_order(): void
    {
        $product = Product::factory()->create(['old_price' => 200, 'new_price' => 100]);
        $order = (new OrderService)->place($product, $this->data(['quantity' => 2]));

        $product->update(['new_price' => 50]);

        $this->assertSame('100.00', $order->fresh()->unit_price);
        $this->assertSame('200.00', $order->fresh()->total);
    }
}
