<?php

namespace Tests\Unit\Models;

use App\Models\Category;
use App\Models\Product;
use App\Models\Subcategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SubcategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_slug_is_generated_and_unique_across_categories(): void
    {
        $a = Subcategory::factory()->create(['name' => 'Shoes']);
        $b = Subcategory::factory()->create(['name' => 'Shoes']);

        $this->assertSame('shoes', $a->slug);
        $this->assertSame('shoes-2', $b->slug);
    }

    public function test_belongs_to_a_category(): void
    {
        $category = Category::factory()->create();
        $sub = Subcategory::factory()->for($category)->create();

        $this->assertTrue($sub->category->is($category));
    }

    public function test_has_many_products(): void
    {
        $sub = Subcategory::factory()->create();
        $product = Product::factory()->create(['subcategory_id' => $sub->id]);

        $this->assertTrue($sub->products->contains($product));
    }

    public function test_deleting_subcategory_deletes_products_and_their_images(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('products/b.jpg', 'x');

        $sub = Subcategory::factory()->create();
        Product::factory()->create(['subcategory_id' => $sub->id, 'image' => 'products/b.jpg']);

        $sub->delete();

        $this->assertDatabaseCount('products', 0);
        Storage::disk('public')->assertMissing('products/b.jpg');
    }

    public function test_deleting_subcategory_keeps_the_category_and_siblings(): void
    {
        $category = Category::factory()->create();
        $keep = Subcategory::factory()->for($category)->create();
        $drop = Subcategory::factory()->for($category)->create();

        $drop->delete();

        $this->assertModelExists($category);
        $this->assertModelExists($keep);
        $this->assertModelMissing($drop);
    }
}
