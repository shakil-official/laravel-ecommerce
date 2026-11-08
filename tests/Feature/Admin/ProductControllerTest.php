<?php

namespace Tests\Feature\Admin;

use App\Models\Product;
use App\Models\User;
use App\Models\Subcategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
        Storage::fake('public');
    }

    private function payload(Subcategory $sub, array $override = []): array
    {
        return array_merge([
            'category_id' => $sub->category_id,
            'subcategory_id' => $sub->id,
            'name' => 'Samsung Galaxy S24',
            'description' => 'Flagship phone with a great camera.',
            'old_price' => 120000,
            'new_price' => 99999.50,
            'image' => UploadedFile::fake()->image('phone.jpg', 600, 400),
        ], $override);
    }

    public function test_index_lists_products(): void
    {
        $product = Product::factory()->create(['name' => 'Listed Product']);

        $this->get(route('admin.products.index'))
            ->assertOk()
            ->assertSee('Listed Product')
            ->assertSee($product->slug);
    }

    public function test_index_filters_by_category_and_name(): void
    {
        $a = Product::factory()->create(['name' => 'Alpha Item']);
        Product::factory()->create(['name' => 'Beta Item']);

        $this->get(route('admin.products.index', ['category_id' => $a->category_id]))
            ->assertSee('Alpha Item')->assertDontSee('Beta Item');

        $this->get(route('admin.products.index', ['q' => 'Beta']))
            ->assertSee('Beta Item')->assertDontSee('Alpha Item');

        $this->get(route('admin.products.index', ['subcategory_id' => $a->subcategory_id]))
            ->assertSee('Alpha Item')->assertDontSee('Beta Item');
    }

    public function test_create_shows_form_with_category_and_subcategory_data(): void
    {
        $sub = Subcategory::factory()->create(['name' => 'Phones']);

        $this->get(route('admin.products.create', ['category_id' => $sub->category_id, 'subcategory_id' => $sub->id]))
            ->assertOk()
            ->assertSee($sub->category->name)
            ->assertSee('Phones')
            ->assertSee('enctype="multipart/form-data"', false);
    }

    public function test_store_creates_product_and_uploads_image(): void
    {
        $sub = Subcategory::factory()->create();

        $this->post(route('admin.products.store'), $this->payload($sub))
            ->assertRedirect(route('admin.products.index'))
            ->assertSessionHas('success');

        $product = Product::firstOrFail();
        $this->assertSame('samsung-galaxy-s24', $product->slug);
        $this->assertSame($sub->category_id, $product->category_id);
        $this->assertSame($sub->id, $product->subcategory_id);
        Storage::disk('public')->assertExists($product->image);
    }

    public function test_store_requires_an_image(): void
    {
        $sub = Subcategory::factory()->create();

        $this->post(route('admin.products.store'), $this->payload($sub, ['image' => null]))
            ->assertSessionHasErrors('image');

        $this->assertDatabaseCount('products', 0);
    }

    public function test_store_rejects_non_image_upload(): void
    {
        $sub = Subcategory::factory()->create();

        $this->post(route('admin.products.store'), $this->payload($sub, ['image' => UploadedFile::fake()->create('a.pdf', 5, 'application/pdf')]))
            ->assertSessionHasErrors('image');
    }

    public function test_store_rejects_new_price_above_old_price(): void
    {
        $sub = Subcategory::factory()->create();

        $this->post(route('admin.products.store'), $this->payload($sub, ['old_price' => 10, 'new_price' => 20]))
            ->assertSessionHasErrors('new_price');
    }

    public function test_store_rejects_subcategory_from_another_category(): void
    {
        $sub = Subcategory::factory()->create();
        $foreign = Subcategory::factory()->create();

        $this->post(route('admin.products.store'), $this->payload($sub, ['subcategory_id' => $foreign->id]))
            ->assertSessionHasErrors('subcategory_id');

        $this->assertDatabaseCount('products', 0);
    }

    public function test_store_keeps_old_input_on_validation_error(): void
    {
        $sub = Subcategory::factory()->create();

        $this->from(route('admin.products.create'))
            ->post(route('admin.products.store'), $this->payload($sub, ['name' => '', 'description' => 'Kept description text']))
            ->assertRedirect(route('admin.products.create'))
            ->assertSessionHasInput('description', 'Kept description text');
    }

    public function test_show_displays_product_details(): void
    {
        $product = Product::factory()->create(['name' => 'Detail Product']);

        $this->get(route('admin.products.show', $product))
            ->assertOk()
            ->assertSee('Detail Product')
            ->assertSee($product->category->name)
            ->assertSee($product->subcategory->name);
    }

    public function test_edit_shows_prefilled_form(): void
    {
        $product = Product::factory()->create(['name' => 'Editable']);

        $this->get(route('admin.products.edit', $product))
            ->assertOk()
            ->assertSee('value="Editable"', false);
    }

    public function test_update_without_new_image_keeps_the_old_one(): void
    {
        $sub = Subcategory::factory()->create();
        Storage::disk('public')->put('products/keep.jpg', 'x');
        $product = Product::factory()->create(['subcategory_id' => $sub->id, 'image' => 'products/keep.jpg']);
        $originalSlug = $product->slug;

        $this->put(route('admin.products.update', $product), $this->payload($sub, ['name' => 'Renamed', 'image' => null]))
            ->assertRedirect(route('admin.products.index'))
            ->assertSessionHas('success');

        $product->refresh();
        $this->assertSame('Renamed', $product->name);
        $this->assertSame($originalSlug, $product->slug);
        $this->assertSame('products/keep.jpg', $product->image);
        Storage::disk('public')->assertExists('products/keep.jpg');
    }

    public function test_update_with_new_image_replaces_the_old_file(): void
    {
        $sub = Subcategory::factory()->create();
        Storage::disk('public')->put('products/old.jpg', 'x');
        $product = Product::factory()->create(['subcategory_id' => $sub->id, 'image' => 'products/old.jpg']);

        $this->put(route('admin.products.update', $product), $this->payload($sub))->assertSessionHasNoErrors();

        $product->refresh();
        $this->assertNotSame('products/old.jpg', $product->image);
        Storage::disk('public')->assertMissing('products/old.jpg');
        Storage::disk('public')->assertExists($product->image);
    }

    public function test_update_can_move_product_to_another_category_and_subcategory(): void
    {
        $product = Product::factory()->create();
        $target = Subcategory::factory()->create();

        $this->put(route('admin.products.update', $product), $this->payload($target, ['image' => null]))
            ->assertSessionHasNoErrors();

        $product->refresh();
        $this->assertSame($target->id, $product->subcategory_id);
        $this->assertSame($target->category_id, $product->category_id);
    }

    public function test_update_validates_input(): void
    {
        $product = Product::factory()->create();

        $this->put(route('admin.products.update', $product), ['name' => ''])
            ->assertSessionHasErrors(['name', 'category_id', 'subcategory_id', 'old_price', 'new_price', 'description']);
    }

    public function test_destroy_deletes_product_and_image_file(): void
    {
        Storage::disk('public')->put('products/gone.jpg', 'x');
        $product = Product::factory()->create(['image' => 'products/gone.jpg']);

        $this->delete(route('admin.products.destroy', $product))
            ->assertRedirect(route('admin.products.index'))
            ->assertSessionHas('success');

        $this->assertModelMissing($product);
        Storage::disk('public')->assertMissing('products/gone.jpg');
    }
}
