<?php

namespace Tests\Unit\Models;

use App\Models\Category;
use App\Models\Product;
use App\Models\Subcategory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_slug_is_generated_from_name_on_create(): void
    {
        $category = Category::create(['name' => 'Home & Living']);

        $this->assertSame('home-living', $category->slug);
    }

    public function test_slug_is_made_unique_when_collision_occurs(): void
    {
        Category::create(['name' => 'Toys']);
        $second = Category::create(['name' => 'Toys!']);

        $this->assertSame('toys-2', $second->slug);
    }

    public function test_slug_stays_stable_when_name_changes(): void
    {
        $category = Category::create(['name' => 'Books']);
        $category->update(['name' => 'Magazines']);

        $this->assertSame('books', $category->fresh()->slug);
    }

    public function test_slug_is_kept_when_name_is_unchanged(): void
    {
        $category = Category::create(['name' => 'Books']);
        $category->update(['description' => 'Updated description']);

        $this->assertSame('books', $category->fresh()->slug);
    }

    public function test_explicit_slug_is_respected_on_create(): void
    {
        $category = Category::create(['name' => 'Books', 'slug' => 'custom-slug']);

        $this->assertSame('custom-slug', $category->slug);
    }

    public function test_unique_slug_ignores_the_given_id(): void
    {
        $category = Category::create(['name' => 'Books']);

        $this->assertSame('books', Category::uniqueSlug('Books', $category->id));
        $this->assertSame('books-2', Category::uniqueSlug('Books'));
    }

    public function test_unique_slug_falls_back_for_non_latin_empty_slug(): void
    {
        $this->assertSame('item', Category::uniqueSlug('???'));
    }

    public function test_subcategories_relation_is_has_many(): void
    {
        $category = Category::factory()->create();
        $sub = Subcategory::factory()->for($category)->create();

        $this->assertInstanceOf(HasMany::class, $category->subcategories());
        $this->assertTrue($category->subcategories->contains($sub));
    }

    public function test_products_relation_returns_products_of_the_category(): void
    {
        $sub = Subcategory::factory()->create();
        $product = Product::factory()->create(['subcategory_id' => $sub->id]);

        $this->assertTrue($sub->category->products->contains($product));
    }

    public function test_deleting_category_cascades_to_subcategories_products_and_images(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('products/a.jpg', 'x');

        $sub = Subcategory::factory()->create();
        Product::factory()->create(['subcategory_id' => $sub->id, 'image' => 'products/a.jpg']);

        $sub->category->delete();

        $this->assertDatabaseCount('categories', 0);
        $this->assertDatabaseCount('subcategories', 0);
        $this->assertDatabaseCount('products', 0);
        Storage::disk('public')->assertMissing('products/a.jpg');
    }
}
