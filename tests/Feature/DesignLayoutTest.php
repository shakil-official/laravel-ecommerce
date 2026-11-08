<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Layout-level behaviour of the redesigned storefront and admin area.
 */
class DesignLayoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_storefront_header_lists_all_categories(): void
    {
        Category::factory()->create(['name' => 'Zed Gadgets']);
        Category::factory()->create(['name' => 'Quark Garden']);

        $this->get(route('shop.index'))
            ->assertOk()
            ->assertSee('Zed Gadgets')
            ->assertSee('Quark Garden')
            ->assertSee('All products');
    }

    public function test_storefront_loads_local_assets_without_cdn(): void
    {
        $html = $this->get(route('shop.index'))->assertOk()->getContent();

        $this->assertStringContainsString('vendor/bootstrap/css/bootstrap.min.css', $html);
        $this->assertStringContainsString('vendor/bootstrap-icons/bootstrap-icons.min.css', $html);
        $this->assertStringContainsString('css/app.css', $html);
        $this->assertStringNotContainsString('cdn.jsdelivr.net', $html);
    }

    public function test_bundled_frontend_assets_exist(): void
    {
        foreach ([
            'vendor/bootstrap/css/bootstrap.min.css',
            'vendor/bootstrap/js/bootstrap.bundle.min.js',
            'vendor/bootstrap-icons/bootstrap-icons.min.css',
            'vendor/bootstrap-icons/fonts/bootstrap-icons.woff2',
            'vendor/fonts/inter/inter.css',
            'vendor/fonts/inter/inter-latin-400-normal.woff2',
            'css/app.css',
            'images/placeholder.svg',
        ] as $file) {
            $this->assertFileExists(public_path($file), $file);
        }
    }

    public function test_home_page_shows_hero_only_without_filters(): void
    {
        Product::factory()->create();

        $this->get(route('shop.index'))->assertSee('Everything you need,', false)->assertSee('Shop now');
        $this->get(route('shop.index', ['q' => 'zzz']))->assertDontSee('Everything you need,', false);
    }

    public function test_product_page_has_live_total_and_savings(): void
    {
        $product = Product::factory()->create(['old_price' => 1500, 'new_price' => 1000]);

        $this->get(route('shop.product', $product->slug))
            ->assertSee('id="order-total"', false)
            ->assertSee('data-unit-price="1000.00"', false)
            ->assertSee('You save')
            ->assertSee('500.00');
    }

    public function test_admin_pages_use_the_sidebar_layout(): void
    {
        $this->actingAs(User::factory()->create(['name' => 'Boss Admin', 'email' => 'boss@example.com']));

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('class="sidebar"', false)
            ->assertSee('Boss Admin')
            ->assertSee('boss@example.com')
            ->assertSee('View storefront')
            ->assertSee('noindex', false);
    }

    public function test_sidebar_shows_pending_order_count(): void
    {
        $this->actingAs(User::factory()->create());
        Order::factory()->count(3)->create();
        Order::factory()->status(\App\Enums\OrderStatus::Delivered)->create();

        $this->get(route('admin.dashboard'))->assertSee('title="Pending orders">3</span>', false);
    }

    public function test_sidebar_hides_badge_when_there_are_no_pending_orders(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('admin.dashboard'))->assertDontSee('title="Pending orders"', false);
    }

    public function test_login_page_uses_split_auth_layout(): void
    {
        $this->get(route('login'))->assertOk()->assertSee('auth-wrap', false)->assertSee('Back to the store');
    }

    public function test_404_page_is_styled_and_has_navigation(): void
    {
        $this->get('/product/missing')->assertNotFound()->assertSee('404')->assertSee('Back to shop');
    }
}
