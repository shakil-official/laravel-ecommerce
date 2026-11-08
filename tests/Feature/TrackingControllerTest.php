<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrackingControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_tracking_form_is_public(): void
    {
        $this->get(route('shop.track'))
            ->assertOk()
            ->assertSee('Track your order')
            ->assertSee('name="token"', false)
            ->assertSee(route('shop.track.lookup'), false);
    }

    public function test_lookup_with_valid_token_redirects_to_tracking_page(): void
    {
        $order = Order::factory()->create();

        $this->post(route('shop.track.lookup'), ['token' => $order->tracking_token])
            ->assertRedirect(route('shop.track.show', $order->tracking_token));
    }

    public function test_lookup_accepts_lowercase_and_spaces(): void
    {
        $order = Order::factory()->create();
        $typed = strtolower(str_replace('-', ' ', $order->tracking_token));

        $this->post(route('shop.track.lookup'), ['token' => $typed])
            ->assertRedirect(route('shop.track.show', $order->tracking_token));
    }

    public function test_lookup_with_unknown_token_shows_error_and_keeps_input(): void
    {
        $this->from(route('shop.track'))
            ->post(route('shop.track.lookup'), ['token' => 'AAAA-BBBB-CCCC'])
            ->assertRedirect(route('shop.track'))
            ->assertSessionHasErrors('token')
            ->assertSessionHasInput('token', 'AAAA-BBBB-CCCC');
    }

    public function test_lookup_validates_token_format(): void
    {
        $this->post(route('shop.track.lookup'), [])->assertSessionHasErrors('token');
        $this->post(route('shop.track.lookup'), ['token' => 'abc'])->assertSessionHasErrors('token');
        $this->post(route('shop.track.lookup'), ['token' => str_repeat('A', 20)])->assertSessionHasErrors('token');
    }

    public function test_lookup_is_throttled_against_token_guessing(): void
    {
        for ($i = 0; $i < 20; $i++) {
            $this->post(route('shop.track.lookup'), ['token' => 'AAAA-BBBB-CCCC']);
        }

        $this->post(route('shop.track.lookup'), ['token' => 'AAAA-BBBB-CCCC'])->assertStatus(429);
    }

    public function test_tracking_page_shows_order_details_and_hides_full_phone(): void
    {
        $order = Order::factory()->create([
            'customer_name' => 'Rahim Uddin',
            'phone' => '01712345678',
            'product_name' => 'Nice Phone',
            'quantity' => 2,
            'total' => 2000,
        ]);

        $this->get(route('shop.track.show', $order->tracking_token))
            ->assertOk()
            ->assertSee($order->order_number)
            ->assertSee('Nice Phone')
            ->assertSee('Rahim Uddin')
            ->assertSee('017******78')
            ->assertDontSee('01712345678')
            ->assertSee('2,000.00');
    }

    public function test_tracking_page_shows_payment_status(): void
    {
        $order = Order::factory()->create(['payment_status' => PaymentStatus::Paid]);

        $this->get(route('shop.track.show', $order->tracking_token))->assertSee('Payment: Paid');
    }

    public function test_unknown_token_url_is_404(): void
    {
        $this->get('/track/NOPE-NOPE-NOPE')->assertNotFound();
    }

    public function test_order_number_cannot_be_used_in_the_tracking_url(): void
    {
        $order = Order::factory()->create();

        $this->get('/track/'.$order->order_number)->assertNotFound();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('timelineProvider')]
    public function test_timeline_marks_completed_and_current_steps(OrderStatus $status, int $done, int $current): void
    {
        $order = Order::factory()->status($status)->create();

        $html = $this->get(route('shop.track.show', $order->tracking_token))->assertOk()->getContent();

        $this->assertSame($done, substr_count($html, '<li class="done">'), "done steps for {$status->value}");
        $this->assertSame($current, substr_count($html, '<li class="current">'), "current step for {$status->value}");
    }

    public static function timelineProvider(): array
    {
        return [
            'pending' => [OrderStatus::Pending, 0, 1],
            'confirmed' => [OrderStatus::Confirmed, 1, 1],
            'packaging' => [OrderStatus::Packaging, 2, 1],
            'shipped' => [OrderStatus::Shipped, 3, 1],
            'delivered' => [OrderStatus::Delivered, 5, 0],
        ];
    }

    public function test_cancelled_order_shows_cancelled_banner_instead_of_timeline(): void
    {
        $order = Order::factory()->status(OrderStatus::Cancelled)->create();

        $html = $this->get(route('shop.track.show', $order->tracking_token))
            ->assertSee('Order cancelled')
            ->getContent();

        $this->assertStringNotContainsString('<ol class="timeline">', $html);
    }

    public function test_status_change_in_admin_is_visible_to_the_customer(): void
    {
        $order = Order::factory()->create();
        $url = route('shop.track.show', $order->tracking_token);

        $this->get($url)->assertSee('We have received your order');

        $order->update(['status' => OrderStatus::Shipped]);

        $this->get($url)->assertSee('Your order is on its way.');
    }

    public function test_thanks_page_shows_token_and_tracking_link(): void
    {
        $order = Order::factory()->create();

        $this->get(route('shop.order.thanks', $order->tracking_token))
            ->assertSee($order->tracking_token)
            ->assertSee(route('shop.track.show', $order->tracking_token), false)
            ->assertSee('Your tracking token');
    }

    public function test_navigation_links_to_tracking_page(): void
    {
        $this->get(route('shop.index'))->assertSee(route('shop.track'), false)->assertSee('Track order');
    }
}
