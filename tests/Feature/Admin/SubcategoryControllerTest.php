<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\Subcategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubcategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    public function test_index_lists_subcategories_with_parent(): void
    {
        $sub = Subcategory::factory()->create(['name' => 'Phones']);

        $this->get(route('admin.subcategories.index'))
            ->assertOk()
            ->assertSee('Phones')
            ->assertSee($sub->category->name);
    }

    public function test_index_filters_by_category_and_name(): void
    {
        $a = Subcategory::factory()->create(['name' => 'Phones']);
        Subcategory::factory()->create(['name' => 'Laptops']);

        $this->get(route('admin.subcategories.index', ['category_id' => $a->category_id]))
            ->assertSee('Phones')->assertDontSee('Laptops');

        $this->get(route('admin.subcategories.index', ['q' => 'Lap']))
            ->assertSee('Laptops')->assertDontSee('Phones');
    }

    public function test_create_shows_form_with_preselected_category(): void
    {
        $category = Category::factory()->create(['name' => 'Electronics']);

        $this->get(route('admin.subcategories.create', ['category_id' => $category->id]))
            ->assertOk()
            ->assertSee('value="'.$category->id.'" selected', false);
    }

    public function test_store_creates_subcategory_under_category(): void
    {
        $category = Category::factory()->create();

        $this->post(route('admin.subcategories.store'), ['category_id' => $category->id, 'name' => 'Phones'])
            ->assertRedirect(route('admin.subcategories.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('subcategories', ['category_id' => $category->id, 'name' => 'Phones', 'slug' => 'phones']);
    }

    public function test_store_requires_existing_category(): void
    {
        $this->post(route('admin.subcategories.store'), ['category_id' => 999, 'name' => 'Phones'])
            ->assertSessionHasErrors('category_id');

        $this->assertDatabaseCount('subcategories', 0);
    }

    public function test_store_rejects_duplicate_name_in_same_category(): void
    {
        $sub = Subcategory::factory()->create(['name' => 'Phones']);

        $this->post(route('admin.subcategories.store'), ['category_id' => $sub->category_id, 'name' => 'Phones'])
            ->assertSessionHasErrors('name');
    }

    public function test_store_allows_same_name_in_another_category(): void
    {
        Subcategory::factory()->create(['name' => 'Phones']);
        $other = Category::factory()->create();

        $this->post(route('admin.subcategories.store'), ['category_id' => $other->id, 'name' => 'Phones'])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('subcategories', 2);
    }

    public function test_show_displays_subcategory_and_its_products(): void
    {
        $sub = Subcategory::factory()->create();
        Product::factory()->create(['subcategory_id' => $sub->id, 'name' => 'Pixel Phone']);

        $this->get(route('admin.subcategories.show', $sub))
            ->assertOk()
            ->assertSee($sub->name)
            ->assertSee('Pixel Phone');
    }

    public function test_edit_shows_prefilled_form(): void
    {
        $sub = Subcategory::factory()->create(['name' => 'Phones']);

        $this->get(route('admin.subcategories.edit', $sub))->assertOk()->assertSee('value="Phones"', false);
    }

    public function test_update_changes_name(): void
    {
        $sub = Subcategory::factory()->create(['name' => 'Phones']);

        $this->put(route('admin.subcategories.update', $sub), ['category_id' => $sub->category_id, 'name' => 'Smartphones'])
            ->assertRedirect(route('admin.subcategories.index'));

        $this->assertDatabaseHas('subcategories', ['id' => $sub->id, 'name' => 'Smartphones', 'slug' => 'phones']);
    }

    public function test_update_allows_same_name(): void
    {
        $sub = Subcategory::factory()->create(['name' => 'Phones']);

        $this->put(route('admin.subcategories.update', $sub), ['category_id' => $sub->category_id, 'name' => 'Phones'])
            ->assertSessionHasNoErrors();
    }

    public function test_moving_subcategory_moves_its_products_to_the_new_category(): void
    {
        $sub = Subcategory::factory()->create();
        $product = Product::factory()->create(['subcategory_id' => $sub->id]);
        $newCategory = Category::factory()->create();

        $this->put(route('admin.subcategories.update', $sub), ['category_id' => $newCategory->id, 'name' => $sub->name])
            ->assertSessionHasNoErrors();

        $this->assertSame($newCategory->id, $product->fresh()->category_id);
    }

    public function test_destroy_deletes_subcategory_and_products_but_not_category(): void
    {
        $sub = Subcategory::factory()->create();
        Product::factory()->create(['subcategory_id' => $sub->id]);

        $this->delete(route('admin.subcategories.destroy', $sub))
            ->assertRedirect(route('admin.subcategories.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseCount('subcategories', 0);
        $this->assertDatabaseCount('products', 0);
        $this->assertDatabaseCount('categories', 1);
    }
}
