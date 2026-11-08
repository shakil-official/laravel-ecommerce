<?php

namespace Tests\Unit\Models;

use App\Models\Category;
use App\Models\Product;
use App\Models\Subcategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_slug_is_generated_from_name(): void
    {
        $product = Product::factory()->create(['name' => 'Samsung Galaxy S24']);

        $this->assertSame('samsung-galaxy-s24', $product->slug);
    }

    public function test_duplicate_names_get_numbered_slugs(): void
    {
        Product::factory()->create(['name' => 'Same Name']);
        $second = Product::factory()->create(['name' => 'Same Name']);

        $this->assertSame('same-name-2', $second->slug);
    }

    public function test_factory_derives_category_from_subcategory(): void
    {
        $sub = Subcategory::factory()->create();
        $product = Product::factory()->create(['subcategory_id' => $sub->id]);

        $this->assertSame($sub->category_id, $product->category_id);
    }

    public function test_belongs_to_category_and_subcategory(): void
    {
        $product = Product::factory()->create();

        $this->assertInstanceOf(Category::class, $product->category);
        $this->assertInstanceOf(Subcategory::class, $product->subcategory);
    }

    public function test_prices_are_cast_to_two_decimals(): void
    {
        $product = Product::factory()->create(['old_price' => 100, 'new_price' => 80.5]);

        $this->assertSame('100.00', $product->fresh()->old_price);
        $this->assertSame('80.50', $product->fresh()->new_price);
    }

    public function test_has_discount_accessor(): void
    {
        $this->assertTrue(Product::factory()->make(['old_price' => 100, 'new_price' => 80])->has_discount);
        $this->assertFalse(Product::factory()->make(['old_price' => 100, 'new_price' => 100])->has_discount);
    }

    public function test_discount_percent_accessor(): void
    {
        $this->assertSame(20, Product::factory()->make(['old_price' => 100, 'new_price' => 80])->discount_percent);
        $this->assertSame(0, Product::factory()->make(['old_price' => 100, 'new_price' => 100])->discount_percent);
        $this->assertSame(0, Product::factory()->make(['old_price' => 0, 'new_price' => 0])->discount_percent);
    }

    public function test_image_url_returns_storage_url_when_file_exists(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('products/real.jpg', 'x');

        $product = Product::factory()->make(['image' => 'products/real.jpg']);

        $this->assertStringContainsString('products/real.jpg', $product->image_url);
    }

    public function test_image_url_falls_back_to_placeholder(): void
    {
        Storage::fake('public');

        $this->assertStringContainsString('placeholder.svg', Product::factory()->make(['image' => null])->image_url);
    }

    public function test_delete_image_file_removes_the_file(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('products/x.jpg', 'x');

        Product::factory()->make(['image' => 'products/x.jpg'])->deleteImageFile();

        Storage::disk('public')->assertMissing('products/x.jpg');
    }

    public function test_delete_image_file_is_safe_without_image(): void
    {
        Product::factory()->make(['image' => null])->deleteImageFile();

        $this->assertTrue(true);
    }

    public function test_deleting_a_product_removes_its_image(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('products/y.jpg', 'x');
        $product = Product::factory()->create(['image' => 'products/y.jpg']);

        $product->delete();

        Storage::disk('public')->assertMissing('products/y.jpg');
    }

    public function test_full_price_factory_state_has_no_discount(): void
    {
        $this->assertFalse(Product::factory()->fullPrice()->make()->has_discount);
    }
}
