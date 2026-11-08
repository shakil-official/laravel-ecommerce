<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\ProductRequest;
use App\Models\Product;
use App\Models\Subcategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ProductRequestTest extends TestCase
{
    use RefreshDatabase, RequestTestHelper;

    private function valid(Subcategory $sub, array $override = []): array
    {
        return array_merge([
            'category_id' => $sub->category_id,
            'subcategory_id' => $sub->id,
            'name' => 'Nice Phone',
            'description' => 'A very nice phone with a long description.',
            'old_price' => 100,
            'new_price' => 80,
        ], $override);
    }

    public function test_new_price_must_be_at_least_one_paisa(): void
    {
        $sub = Subcategory::factory()->create();
        $v = $this->validate(ProductRequest::class, $this->valid($sub, ['old_price' => 100, 'new_price' => 0]));

        $this->assertTrue($v->errors()->has('new_price'));
    }

    public function test_authorize_is_true(): void
    {
        $this->assertTrue((new ProductRequest)->authorize());
    }

    public function test_valid_create_data_passes_with_image(): void
    {
        $sub = Subcategory::factory()->create();
        $data = $this->valid($sub, ['image' => UploadedFile::fake()->image('p.jpg')]);

        $this->assertTrue($this->validate(ProductRequest::class, $data)->passes());
    }

    public function test_image_is_required_on_create(): void
    {
        $sub = Subcategory::factory()->create();

        $v = $this->validate(ProductRequest::class, $this->valid($sub));

        $this->assertSame('Please upload a product image.', $v->errors()->first('image'));
    }

    public function test_image_is_optional_on_update(): void
    {
        $product = Product::factory()->create();

        $v = $this->validate(ProductRequest::class, $this->valid($product->subcategory), 'PUT', ['product' => $product]);

        $this->assertTrue($v->passes());
    }

    public function test_image_must_be_an_allowed_type(): void
    {
        $sub = Subcategory::factory()->create();
        $data = $this->valid($sub, ['image' => UploadedFile::fake()->create('doc.pdf', 10, 'application/pdf')]);

        $this->assertTrue($this->validate(ProductRequest::class, $data)->errors()->has('image'));
    }

    public function test_image_must_not_exceed_max_size(): void
    {
        $sub = Subcategory::factory()->create();
        $data = $this->valid($sub, ['image' => UploadedFile::fake()->image('big.jpg')->size(config('shop.image.max_kb') + 1)]);

        $this->assertTrue($this->validate(ProductRequest::class, $data)->errors()->has('image'));
    }

    public function test_required_fields(): void
    {
        $errors = $this->validate(ProductRequest::class, [])->errors();

        foreach (['category_id', 'subcategory_id', 'name', 'description', 'old_price', 'new_price', 'image'] as $field) {
            $this->assertTrue($errors->has($field), "{$field} should be required");
        }
    }

    public function test_subcategory_must_belong_to_selected_category(): void
    {
        $sub = Subcategory::factory()->create();
        $other = Subcategory::factory()->create();

        $v = $this->validate(ProductRequest::class, $this->valid($sub, ['category_id' => $other->category_id]));

        $this->assertTrue($v->errors()->has('subcategory_id'));
    }

    public function test_prices_must_be_numeric_and_non_negative(): void
    {
        $sub = Subcategory::factory()->create();

        $errors = $this->validate(ProductRequest::class, $this->valid($sub, ['old_price' => 'abc', 'new_price' => -5]))->errors();

        $this->assertTrue($errors->has('old_price'));
        $this->assertTrue($errors->has('new_price'));
    }

    public function test_new_price_may_not_exceed_old_price(): void
    {
        $sub = Subcategory::factory()->create();

        $v = $this->validate(ProductRequest::class, $this->valid($sub, ['old_price' => 100, 'new_price' => 150]));

        $this->assertSame('The new price must be less than or equal to the old price.', $v->errors()->first('new_price'));
    }

    public function test_new_price_equal_to_old_price_is_allowed(): void
    {
        $sub = Subcategory::factory()->create();
        $data = $this->valid($sub, ['old_price' => 100, 'new_price' => 100]);

        $this->assertFalse($this->validate(ProductRequest::class, $data)->errors()->has('new_price'));
    }

    public function test_description_needs_at_least_ten_characters(): void
    {
        $sub = Subcategory::factory()->create();

        $this->assertTrue($this->validate(ProductRequest::class, $this->valid($sub, ['description' => 'short']))->errors()->has('description'));
    }

    public function test_attributes_are_human_readable(): void
    {
        $this->assertSame('old price', (new ProductRequest)->attributes()['old_price']);
    }
}
