<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Subcategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShopControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_groups_products_by_subcategory(): void
    {
        $phones = Subcategory::factory()->create(['name' => 'Phones']);
        $shoes = Subcategory::factory()->create(['name' => 'Shoes']);
        Product::factory()->create(['subcategory_id' => $phones->id, 'name' => 'Pixel']);
        Product::factory()->create(['subcategory_id' => $shoes->id, 'name' => 'Runner']);

        $response = $this->get(route('shop.index'))->assertOk();

        $response->assertSeeInOrder(['Phones', 'Pixel']);
        $response->assertSeeInOrder(['Shoes', 'Runner']);
        $response->assertViewHas('sections', fn ($s) => $s->total() === 2);
    }

    public function test_index_hides_subcategories_without_products(): void
    {
        Subcategory::factory()->create(['name' => 'Empty Section']);

        $this->get(route('shop.index'))->assertOk()->assertDontSee('Empty Section')->assertSee('No products found.');
    }

    public function test_index_can_filter_by_category_slug(): void
    {
        $a = Product::factory()->create(['name' => 'Alpha']);
        Product::factory()->create(['name' => 'Beta']);

        $this->get(route('shop.index', ['category' => $a->category->slug]))
            ->assertSee('Alpha')->assertDontSee('Beta');
    }

    public function test_index_can_search_by_product_name(): void
    {
        Product::factory()->create(['name' => 'Alpha Phone']);
        Product::factory()->create(['name' => 'Beta Shoe']);

        $this->get(route('shop.index', ['q' => 'Phone']))
            ->assertSee('Alpha Phone')->assertDontSee('Beta Shoe');
    }

    public function test_index_paginates_subcategory_sections(): void
    {
        config(['shop.listing_per_page' => 2]);
        Product::factory()->count(3)->create();

        $this->get(route('shop.index'))
            ->assertViewHas('sections', fn ($s) => $s->count() === 2 && $s->lastPage() === 2);
    }

    public function test_subcategory_page_lists_its_products(): void
    {
        $sub = Subcategory::factory()->create(['name' => 'Phones']);
        Product::factory()->create(['subcategory_id' => $sub->id, 'name' => 'Pixel']);
        Product::factory()->create(['name' => 'Unrelated']);

        $this->get(route('shop.subcategory', $sub->slug))
            ->assertOk()
            ->assertSee('Phones')
            ->assertSee('Pixel')
            ->assertDontSee('Unrelated');
    }

    public function test_subcategory_page_returns_404_for_unknown_slug(): void
    {
        $this->get('/subcategory/does-not-exist')->assertNotFound();
    }

    public function test_product_is_reachable_by_slug(): void
    {
        $product = Product::factory()->create(['name' => 'Samsung Galaxy S24', 'old_price' => 100, 'new_price' => 80]);

        $this->get('/product/samsung-galaxy-s24')
            ->assertOk()
            ->assertSee('Samsung Galaxy S24')
            ->assertSee($product->description)
            ->assertSee('-20%')
            ->assertSee('application/ld+json', false);
    }

    public function test_product_page_shows_related_products_only_from_same_subcategory(): void
    {
        $sub = Subcategory::factory()->create();
        $main = Product::factory()->create(['subcategory_id' => $sub->id, 'name' => 'Main Item']);
        Product::factory()->create(['subcategory_id' => $sub->id, 'name' => 'Sibling Item']);
        Product::factory()->create(['name' => 'Stranger Item']);

        $this->get(route('shop.product', $main->slug))
            ->assertSee('Related products')
            ->assertSee('Sibling Item')
            ->assertDontSee('Stranger Item');
    }

    public function test_product_page_returns_404_for_unknown_slug_and_for_id(): void
    {
        $product = Product::factory()->create();

        $this->get('/product/nope')->assertNotFound();
        $this->get('/product/'.$product->id)->assertNotFound();
    }

    public function test_product_without_discount_has_no_badge(): void
    {
        $product = Product::factory()->fullPrice()->create();

        $this->get(route('shop.product', $product->slug))->assertOk()->assertDontSee('badge-discount', false);
    }

    public function test_category_deletion_makes_product_url_404(): void
    {
        $product = Product::factory()->create();
        $slug = $product->slug;
        Category::find($product->category_id)->delete();

        $this->get('/product/'.$slug)->assertNotFound();
    }

    public function test_category_page_is_reachable_by_slug(): void
    {
        $a = Product::factory()->create(['name' => 'Alpha']);
        Product::factory()->create(['name' => 'Beta']);

        $this->get(route('shop.category', $a->category->slug))
            ->assertOk()->assertSee('Alpha')->assertDontSee('Beta');
    }

    public function test_category_page_returns_404_for_unknown_slug(): void
    {
        $this->get('/category/does-not-exist')->assertNotFound();
    }

    public function test_search_treats_percent_literally(): void
    {
        Product::factory()->create(['name' => 'Alpha Phone']);

        $this->get(route('shop.index', ['q' => '%']))->assertOk()->assertDontSee('Alpha Phone');
    }
}
