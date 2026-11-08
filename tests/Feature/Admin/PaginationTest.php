<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Subcategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Every admin table must be paginated and support the "Rows" selector.
 */
class PaginationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['shop.admin_per_page' => 10]);
        $this->actingAs(User::factory()->create());
    }

    public function test_categories_index_is_paginated(): void
    {
        Category::factory()->count(12)->create();

        $this->get(route('admin.categories.index'))
            ->assertViewHas('categories', fn ($p) => $p->count() === 10 && $p->total() === 12)
            ->assertSee('Showing')->assertSee('1–10')->assertSee('of')->assertSee('12');

        $this->get(route('admin.categories.index', ['page' => 2]))
            ->assertViewHas('categories', fn ($p) => $p->count() === 2)
            ->assertSee('11–12');
    }

    public function test_subcategories_index_is_paginated(): void
    {
        Subcategory::factory()->count(12)->create();

        $this->get(route('admin.subcategories.index'))
            ->assertViewHas('subcategories', fn ($p) => $p->count() === 10 && $p->total() === 12);
    }

    public function test_products_index_is_paginated(): void
    {
        Product::factory()->count(12)->create();

        $this->get(route('admin.products.index'))
            ->assertViewHas('products', fn ($p) => $p->count() === 10 && $p->total() === 12);
    }

    public function test_orders_index_is_paginated(): void
    {
        Order::factory()->count(12)->create();

        $this->get(route('admin.orders.index'))
            ->assertViewHas('orders', fn ($p) => $p->count() === 10 && $p->total() === 12);
    }

    public function test_category_detail_table_is_paginated(): void
    {
        $category = Category::factory()->create();
        Subcategory::factory()->count(12)->for($category)->create();

        $this->get(route('admin.categories.show', $category))
            ->assertViewHas('subcategories', fn ($p) => $p->count() === 10 && $p->total() === 12);

        $this->get(route('admin.categories.show', [$category, 'page' => 2]))
            ->assertViewHas('subcategories', fn ($p) => $p->count() === 2);
    }

    public function test_subcategory_detail_table_is_paginated(): void
    {
        $sub = Subcategory::factory()->create();
        Product::factory()->count(12)->create(['subcategory_id' => $sub->id]);

        $this->get(route('admin.subcategories.show', $sub))
            ->assertViewHas('products', fn ($p) => $p->count() === 10 && $p->total() === 12);
    }

    public function test_per_page_option_is_respected(): void
    {
        Category::factory()->count(12)->create();

        $this->get(route('admin.categories.index', ['per_page' => 25]))
            ->assertViewHas('categories', fn ($p) => $p->count() === 12 && $p->perPage() === 25);

        $this->get(route('admin.categories.index', ['per_page' => 50]))
            ->assertViewHas('categories', fn ($p) => $p->perPage() === 50);
    }

    public function test_invalid_per_page_falls_back_to_the_default(): void
    {
        Category::factory()->count(12)->create();

        foreach ([0, -5, 7, 9999, 'abc'] as $bad) {
            $this->get(route('admin.categories.index', ['per_page' => $bad]))
                ->assertViewHas('categories', fn ($p) => $p->perPage() === 10);
        }
    }

    public function test_per_page_is_kept_together_with_filters_in_page_links(): void
    {
        Category::factory()->count(30)->create(['description' => 'x']);

        $this->get(route('admin.categories.index', ['per_page' => 10, 'q' => 'a']))
            ->assertViewHas('categories', fn ($p) => str_contains($p->url(2), 'per_page=10') && str_contains($p->url(2), 'q=a'));
    }

    public function test_footer_renders_rows_selector_and_hidden_filters(): void
    {
        foreach (['Alpha One', 'Alpha Two', 'Alpha Three'] as $name) {
            Category::factory()->create(['name' => $name]);
        }

        $this->get(route('admin.categories.index', ['q' => 'a']))
            ->assertSee('name="per_page"', false)
            ->assertSee('<option value="25"', false)
            ->assertSee('<input type="hidden" name="q" value="a">', false);
    }

    public function test_footer_is_hidden_when_table_is_empty(): void
    {
        $this->get(route('admin.categories.index'))->assertDontSee('name="per_page"', false);
    }
}
