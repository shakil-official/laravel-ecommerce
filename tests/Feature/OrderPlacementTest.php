<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderPlacementTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $override = []): array
    {
        return array_merge([
            'customer_name' => 'Rahim Uddin',
            'phone' => '01712345678',
            'email' => 'rahim@example.com',
            'address' => 'House 12, Road 5, Dhanmondi, Dhaka',
            'quantity' => 2,
            'payment_method' => 'bkash',
            'note' => 'Call first',
        ], $override);
    }

    public function test_product_page_shows_the_order_form(): void
    {
        $product = Product::factory()->create();

        $this->get(route('shop.product', $product->slug))
            ->assertOk()
            ->assertSee('Order now')
            ->assertSee('id="order-form"', false)
            ->assertSee(route('shop.order.store', $product->slug), false)
            ->assertSee('Cash on delivery');
    }

    public function test_store_creates_pending_unpaid_order_and_redirects_to_thanks(): void
    {
        $product = Product::factory()->create(['name' => 'Nice Phone', 'old_price' => 1500, 'new_price' => 1000]);

        $response = $this->post(route('shop.order.store', $product->slug), $this->payload());

        $order = Order::firstOrFail();
        $response->assertRedirect(route('shop.order.thanks', $order->tracking_token));

        $this->assertSame($product->id, $order->product_id);
        $this->assertSame('Nice Phone', $order->product_name);
        $this->assertSame('2000.00', $order->total);
        $this->assertSame(OrderStatus::Pending, $order->status);
        $this->assertSame(PaymentStatus::Unpaid, $order->payment_status);
        $this->assertSame(PaymentMethod::Bkash, $order->payment_method);
        $this->assertSame('Rahim Uddin', $order->customer_name);
    }

    public function test_price_is_taken_from_the_database_not_from_the_request(): void
    {
        $product = Product::factory()->create(['old_price' => 500, 'new_price' => 400]);

        $this->post(route('shop.order.store', $product->slug), $this->payload(['quantity' => 1, 'total' => 1, 'unit_price' => 1, 'status' => 'delivered', 'payment_status' => 'paid']));

        $order = Order::firstOrFail();
        $this->assertSame('400.00', $order->total);
        $this->assertSame(OrderStatus::Pending, $order->status);
        $this->assertSame(PaymentStatus::Unpaid, $order->payment_status);
    }

    public function test_international_phone_format_is_stored_as_local_number(): void
    {
        $product = Product::factory()->create();

        $this->post(route('shop.order.store', $product->slug), $this->payload(['phone' => '+8801712345678']));

        $this->assertSame('01712345678', Order::firstOrFail()->phone);
    }

    public function test_phone_with_spaces_is_normalised(): void
    {
        $product = Product::factory()->create();

        $this->post(route('shop.order.store', $product->slug), $this->payload(['phone' => '017 1234 5678']));

        $this->assertSame('01712345678', Order::firstOrFail()->phone);
    }

    public function test_invalid_order_is_rejected_and_nothing_is_saved(): void
    {
        $product = Product::factory()->create();

        $this->post(route('shop.order.store', $product->slug), $this->payload(['phone' => '123', 'quantity' => 0, 'customer_name' => '']))
            ->assertSessionHasErrors(['phone', 'quantity', 'customer_name']);

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_old_input_is_kept_after_validation_error(): void
    {
        $product = Product::factory()->create();

        $this->post(route('shop.order.store', $product->slug), $this->payload(['phone' => '123']))
            ->assertSessionHasInput('customer_name', 'Rahim Uddin');
    }

    public function test_order_for_unknown_product_is_404(): void
    {
        $this->post('/product/nope/order', $this->payload())->assertNotFound();
    }

    public function test_thanks_page_shows_the_order_summary(): void
    {
        $order = Order::factory()->create(['customer_name' => 'Karim Mia', 'product_name' => 'Cool Item']);

        $this->get(route('shop.order.thanks', $order->tracking_token))
            ->assertOk()
            ->assertSee('Karim Mia')
            ->assertSee($order->order_number)
            ->assertSee('Cool Item');
    }

    public function test_thanks_page_cannot_be_opened_with_the_guessable_order_number(): void
    {
        $order = Order::factory()->create();

        $this->get('/order/'.$order->order_number.'/thanks')->assertNotFound();
    }

    public function test_private_pages_send_no_cache_noindex_and_no_referrer_headers(): void
    {
        $order = Order::factory()->create();

        foreach ([route('shop.order.thanks', $order->tracking_token), route('shop.track.show', $order->tracking_token)] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
                ->assertHeader('Referrer-Policy', 'no-referrer');
        }
    }

    public function test_thanks_page_is_404_for_unknown_order_number(): void
    {
        $this->get('/order/BS-NOPE/thanks')->assertNotFound();
    }

    public function test_order_form_is_throttled(): void
    {
        $product = Product::factory()->create();

        for ($i = 0; $i < 10; $i++) {
            $this->post(route('shop.order.store', $product->slug), $this->payload());
        }

        $this->post(route('shop.order.store', $product->slug), $this->payload())->assertStatus(429);
        $this->assertDatabaseCount('orders', 10);
    }
}
