<?php

namespace Tests\Feature\Auth;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Subcategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Every admin route must be closed to guests.
 */
class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login_for_every_admin_get_route(): void
    {
        $category = Category::factory()->create();
        $sub = Subcategory::factory()->create();
        $product = Product::factory()->create();
        $order = Order::factory()->create();

        $urls = [
            route('admin.dashboard'),
            route('admin.categories.index'), route('admin.categories.create'),
            route('admin.categories.show', $category), route('admin.categories.edit', $category),
            route('admin.subcategories.index'), route('admin.subcategories.create'),
            route('admin.subcategories.show', $sub), route('admin.subcategories.edit', $sub),
            route('admin.products.index'), route('admin.products.create'),
            route('admin.products.show', $product), route('admin.products.edit', $product),
            route('admin.orders.index'), route('admin.orders.show', $order),
        ];

        foreach ($urls as $url) {
            $this->get($url)->assertRedirect(route('login'));
        }
    }

    public function test_guests_cannot_modify_data(): void
    {
        $category = Category::factory()->create();
        $order = Order::factory()->create();

        $this->post(route('admin.categories.store'), ['name' => 'Hack'])->assertRedirect(route('login'));
        $this->put(route('admin.categories.update', $category), ['name' => 'Hack'])->assertRedirect(route('login'));
        $this->delete(route('admin.categories.destroy', $category))->assertRedirect(route('login'));
        $this->put(route('admin.orders.update', $order), ['status' => 'delivered', 'payment_status' => 'paid'])->assertRedirect(route('login'));
        $this->delete(route('admin.orders.destroy', $order))->assertRedirect(route('login'));

        $this->assertModelExists($category);
        $this->assertModelExists($order);
        $this->assertSame('pending', $order->fresh()->status->value);
    }

    public function test_authenticated_user_can_open_admin(): void
    {
        $this->actingAs(User::factory()->create())->get(route('admin.dashboard'))->assertOk();
    }

    public function test_storefront_stays_public(): void
    {
        $product = Product::factory()->create();

        $this->get(route('shop.index'))->assertOk();
        $this->get(route('shop.product', $product->slug))->assertOk();
    }
}
