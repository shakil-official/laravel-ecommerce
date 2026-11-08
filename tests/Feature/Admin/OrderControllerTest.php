<?php

namespace Tests\Feature\Admin;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    public function test_index_lists_orders_with_customer_and_status_controls(): void
    {
        $order = Order::factory()->create(['customer_name' => 'Rahim Uddin', 'product_name' => 'Nice Phone']);

        $this->get(route('admin.orders.index'))
            ->assertOk()
            ->assertSee($order->order_number)
            ->assertSee('Rahim Uddin')
            ->assertSee('Nice Phone')
            ->assertSee('name="status"', false)
            ->assertSee('name="payment_status"', false)
            ->assertSee('Packaging')
            ->assertSee('Refunded');
    }

    public function test_index_shows_empty_state(): void
    {
        $this->get(route('admin.orders.index'))->assertOk()->assertSee('No orders found.');
    }

    public function test_index_shows_newest_orders_first(): void
    {
        $old = Order::factory()->create();
        $new = Order::factory()->create();

        $this->get(route('admin.orders.index'))->assertSeeInOrder([$new->order_number, $old->order_number]);
    }

    public function test_index_filters_by_status(): void
    {
        $pending = Order::factory()->create();
        $shipped = Order::factory()->status(OrderStatus::Shipped)->create();

        $this->get(route('admin.orders.index', ['status' => 'shipped']))
            ->assertSee($shipped->order_number)
            ->assertDontSee($pending->order_number);
    }

    public function test_index_filters_by_payment_status(): void
    {
        $unpaid = Order::factory()->create();
        $paid = Order::factory()->paid()->create();

        $this->get(route('admin.orders.index', ['payment_status' => 'paid']))
            ->assertSee($paid->order_number)
            ->assertDontSee($unpaid->order_number);
    }

    public function test_index_ignores_unknown_filter_values(): void
    {
        Order::factory()->count(2)->create();

        $this->get(route('admin.orders.index', ['status' => 'bogus', 'payment_status' => 'bogus']))
            ->assertOk()
            ->assertViewHas('orders', fn ($o) => $o->total() === 2);
    }

    public function test_index_searches_by_number_name_or_phone(): void
    {
        $a = Order::factory()->create(['customer_name' => 'Rahim Uddin', 'phone' => '01712345678']);
        $b = Order::factory()->create(['customer_name' => 'Karim Mia', 'phone' => '01812345678']);

        $this->get(route('admin.orders.index', ['q' => 'Rahim']))->assertSee($a->order_number)->assertDontSee($b->order_number);
        $this->get(route('admin.orders.index', ['q' => '01812']))->assertSee($b->order_number)->assertDontSee($a->order_number);
        $this->get(route('admin.orders.index', ['q' => $a->order_number]))->assertSee($a->order_number)->assertDontSee($b->order_number);
    }

    public function test_index_is_paginated(): void
    {
        config(['shop.admin_per_page' => 2]);
        Order::factory()->count(3)->create();

        $this->get(route('admin.orders.index'))
            ->assertViewHas('orders', fn ($o) => $o->count() === 2 && $o->total() === 3);
    }

    public function test_show_displays_order_customer_and_product_link(): void
    {
        $product = Product::factory()->create();
        $order = Order::factory()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'customer_name' => 'Rahim Uddin',
            'address' => 'House 12, Dhanmondi, Dhaka',
            'note' => 'Call first',
        ]);

        $this->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee($order->order_number)
            ->assertSee('Rahim Uddin')
            ->assertSee('House 12, Dhanmondi, Dhaka')
            ->assertSee('Call first')
            ->assertSee(route('admin.products.show', $product), false);
    }

    public function test_show_handles_deleted_product(): void
    {
        $order = Order::factory()->create(['product_id' => null, 'product_name' => 'Gone Item']);

        $this->get(route('admin.orders.show', $order))->assertOk()->assertSee('Gone Item')->assertSee('product deleted');
    }

    public function test_update_changes_status_and_payment_status(): void
    {
        $order = Order::factory()->create();

        $this->from(route('admin.orders.index'))
            ->put(route('admin.orders.update', $order), ['status' => 'packaging', 'payment_status' => 'paid'])
            ->assertRedirect(route('admin.orders.index'))
            ->assertSessionHas('success');

        $order->refresh();
        $this->assertSame(OrderStatus::Packaging, $order->status);
        $this->assertSame(PaymentStatus::Paid, $order->payment_status);
    }

    public function test_cancelled_order_cannot_be_marked_paid(): void
    {
        $order = Order::factory()->create(['status' => OrderStatus::Pending, 'payment_status' => PaymentStatus::Unpaid]);

        $this->put(route('admin.orders.update', $order), ['status' => 'cancelled', 'payment_status' => 'paid'])
            ->assertSessionHasErrors('payment_status');

        $this->assertSame(OrderStatus::Pending, $order->fresh()->status);
    }

    public function test_update_works_through_every_status_and_payment_status(): void
    {
        $order = Order::factory()->create();

        foreach (OrderStatus::cases() as $status) {
            foreach (PaymentStatus::cases() as $payment) {
                if ($status === OrderStatus::Cancelled && $payment === PaymentStatus::Paid) {
                    continue;
                }

                $this->put(route('admin.orders.update', $order), ['status' => $status->value, 'payment_status' => $payment->value])
                    ->assertSessionHasNoErrors();

                $order->refresh();
                $this->assertSame($status, $order->status);
                $this->assertSame($payment, $order->payment_status);
            }
        }
    }

    public function test_update_redirects_to_index_when_there_is_no_previous_page(): void
    {
        $order = Order::factory()->create();

        $this->put(route('admin.orders.update', $order), ['status' => 'shipped', 'payment_status' => 'unpaid'])
            ->assertRedirect(route('admin.orders.index'));
    }

    public function test_update_rejects_invalid_values(): void
    {
        $order = Order::factory()->create();

        $this->put(route('admin.orders.update', $order), ['status' => 'teleported', 'payment_status' => ''])
            ->assertSessionHasErrors(['status', 'payment_status']);

        $this->assertSame(OrderStatus::Pending, $order->fresh()->status);
    }

    public function test_update_does_not_change_customer_data_or_total(): void
    {
        $order = Order::factory()->create(['customer_name' => 'Original', 'total' => 500]);

        $this->put(route('admin.orders.update', $order), [
            'status' => 'confirmed', 'payment_status' => 'paid', 'customer_name' => 'Hacked', 'total' => 1,
        ]);

        $this->assertSame('Original', $order->fresh()->customer_name);
        $this->assertSame('500.00', $order->fresh()->total);
    }

    public function test_destroy_deletes_the_order(): void
    {
        $order = Order::factory()->create();

        $this->delete(route('admin.orders.destroy', $order))
            ->assertRedirect(route('admin.orders.index'))
            ->assertSessionHas('success');

        $this->assertModelMissing($order);
    }

    public function test_unknown_order_returns_404(): void
    {
        $this->get(route('admin.orders.show', 999))->assertNotFound();
    }

    public function test_dashboard_shows_order_statistics(): void
    {
        Order::factory()->create();
        Order::factory()->paid()->create(['total' => 1500]);
        Order::factory()->paid()->status(OrderStatus::Delivered)->create(['total' => 500]);

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertViewHas('orderStats', ['orders' => 3, 'pending' => 2, 'revenue' => 2000.0]);
    }
}
