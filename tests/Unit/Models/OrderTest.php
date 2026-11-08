<?php

namespace Tests\Unit\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_number_is_generated_on_create(): void
    {
        $order = Order::factory()->create();

        $this->assertMatchesRegularExpression('/^BS-\d{6}-[A-Z0-9]{5}$/', $order->order_number);
    }

    public function test_explicit_order_number_is_kept(): void
    {
        $order = Order::factory()->create(['order_number' => 'BS-CUSTOM-1']);

        $this->assertSame('BS-CUSTOM-1', $order->order_number);
    }

    public function test_generate_number_is_unique(): void
    {
        $numbers = collect(range(1, 20))->map(fn () => Order::factory()->create()->order_number);

        $this->assertCount(20, $numbers->unique());
        $this->assertNotContains(Order::generateNumber(), $numbers->all());
    }

    public function test_casts_status_payment_status_and_method_to_enums(): void
    {
        $order = Order::factory()->create()->fresh();

        $this->assertSame(OrderStatus::Pending, $order->status);
        $this->assertSame(PaymentStatus::Unpaid, $order->payment_status);
        $this->assertSame(PaymentMethod::Cod, $order->payment_method);
    }

    public function test_money_columns_are_cast_to_two_decimals(): void
    {
        $order = Order::factory()->create(['unit_price' => 10, 'total' => 30])->fresh();

        $this->assertSame('10.00', $order->unit_price);
        $this->assertSame('30.00', $order->total);
    }

    public function test_belongs_to_product(): void
    {
        $product = Product::factory()->create();
        $order = Order::factory()->create(['product_id' => $product->id]);

        $this->assertTrue($order->product->is($product));
    }

    public function test_order_survives_product_deletion_with_snapshot(): void
    {
        $product = Product::factory()->create();
        $order = Order::factory()->create(['product_id' => $product->id, 'product_name' => 'Snapshot Name']);

        $product->delete();
        $order->refresh();

        $this->assertNull($order->product_id);
        $this->assertNull($order->product);
        $this->assertSame('Snapshot Name', $order->product_name);
    }

    public function test_search_scope_matches_number_name_and_phone(): void
    {
        $a = Order::factory()->create(['customer_name' => 'Rahim Uddin', 'phone' => '01712345678', 'order_number' => 'BS-AAA']);
        $b = Order::factory()->create(['customer_name' => 'Karim Mia', 'phone' => '01812345678', 'order_number' => 'BS-BBB']);

        $this->assertSame([$a->id], Order::search('Rahim')->pluck('id')->all());
        $this->assertSame([$b->id], Order::search('01812')->pluck('id')->all());
        $this->assertSame([$b->id], Order::search('BS-BBB')->pluck('id')->all());
        $this->assertCount(2, Order::search('')->get());
        $this->assertCount(2, Order::search(null)->get());
    }

    public function test_factory_states(): void
    {
        $this->assertSame(OrderStatus::Shipped, Order::factory()->status(OrderStatus::Shipped)->make()->status);
        $this->assertSame(PaymentStatus::Paid, Order::factory()->paid()->make()->payment_status);
    }

    public function test_tracking_token_is_generated_on_create(): void
    {
        $order = Order::factory()->create();

        $this->assertMatchesRegularExpression('/^[A-HJ-NP-Z2-9]{4}-[A-HJ-NP-Z2-9]{4}-[A-HJ-NP-Z2-9]{4}$/', $order->tracking_token);
    }

    public function test_explicit_tracking_token_is_kept(): void
    {
        $this->assertSame('ABCD-EFGH-JKMN', Order::factory()->create(['tracking_token' => 'ABCD-EFGH-JKMN'])->tracking_token);
    }

    public function test_tracking_tokens_are_unique(): void
    {
        $tokens = collect(range(1, 25))->map(fn () => Order::factory()->create()->tracking_token);

        $this->assertCount(25, $tokens->unique());
        $this->assertNotContains(Order::generateToken(), $tokens->all());
    }

    public function test_tracking_token_has_no_ambiguous_characters(): void
    {
        foreach (range(1, 30) as $i) {
            $this->assertDoesNotMatchRegularExpression('/[01OIL]/', str_replace('-', '', Order::generateToken()));
        }
    }

    public function test_normalize_token(): void
    {
        $this->assertSame('K7M2-9QXA-W4TP', Order::normalizeToken('k7m2-9qxa-w4tp'));
        $this->assertSame('K7M2-9QXA-W4TP', Order::normalizeToken(' k7m2 9qxa w4tp '));
        $this->assertSame('K7M2-9QXA-W4TP', Order::normalizeToken('K7M29QXAW4TP'));
        $this->assertSame('AB', Order::normalizeToken('a b'));
        $this->assertSame('', Order::normalizeToken('---'));
    }

    public function test_tracking_url_accessor(): void
    {
        $order = Order::factory()->create(['tracking_token' => 'ABCD-EFGH-JKMN']);

        $this->assertSame(route('shop.track.show', 'ABCD-EFGH-JKMN'), $order->tracking_url);
    }

    public function test_masked_phone_accessor(): void
    {
        $this->assertSame('017******78', Order::factory()->make(['phone' => '01712345678'])->masked_phone);
        $this->assertSame('+88*********78', Order::factory()->make(['phone' => '+8801712345678'])->masked_phone);
        $this->assertSame('1234', Order::factory()->make(['phone' => '1234'])->masked_phone);
    }

    public function test_search_scope_also_matches_tracking_token(): void
    {
        $a = Order::factory()->create(['tracking_token' => 'AAAA-BBBB-CCCC']);
        Order::factory()->create(['tracking_token' => 'DDDD-EEEE-FFFF']);

        $this->assertSame([$a->id], Order::search('BBBB')->pluck('id')->all());
    }
}
