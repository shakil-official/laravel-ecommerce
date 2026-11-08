<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\Subcategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    public function test_index_lists_categories_with_counts(): void
    {
        $category = Category::factory()->create(['name' => 'Gadgets']);
        Product::factory()->create(['subcategory_id' => Subcategory::factory()->for($category)->create()->id]);

        $this->get(route('admin.categories.index'))
            ->assertOk()
            ->assertSee('Gadgets')
            ->assertViewHas('categories', fn ($c) => $c->first()->subcategories_count === 1 && $c->first()->products_count === 1);
    }

    public function test_index_can_search_by_name(): void
    {
        Category::factory()->create(['name' => 'Gadgets']);
        Category::factory()->create(['name' => 'Garden']);

        $this->get(route('admin.categories.index', ['q' => 'Gadg']))
            ->assertSee('Gadgets')
            ->assertDontSee('Garden');
    }

    public function test_index_is_paginated(): void
    {
        config(['shop.admin_per_page' => 2]);
        Category::factory()->count(3)->create();

        $this->get(route('admin.categories.index'))
            ->assertViewHas('categories', fn ($c) => $c->count() === 2 && $c->total() === 3);
    }

    public function test_create_shows_the_form(): void
    {
        $this->get(route('admin.categories.create'))->assertOk()->assertSee('Create category');
    }

    public function test_store_creates_category_and_redirects(): void
    {
        $this->post(route('admin.categories.store'), ['name' => 'Books', 'description' => 'Paper'])
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('categories', ['name' => 'Books', 'slug' => 'books', 'description' => 'Paper']);
    }

    public function test_store_validates_input(): void
    {
        $this->from(route('admin.categories.create'))
            ->post(route('admin.categories.store'), ['name' => ''])
            ->assertRedirect(route('admin.categories.create'))
            ->assertSessionHasErrors('name');

        $this->assertDatabaseCount('categories', 0);
    }

    public function test_store_rejects_duplicate_name(): void
    {
        Category::factory()->create(['name' => 'Books']);

        $this->post(route('admin.categories.store'), ['name' => 'Books'])->assertSessionHasErrors('name');

        $this->assertDatabaseCount('categories', 1);
    }

    public function test_show_displays_category_and_its_subcategories(): void
    {
        $sub = Subcategory::factory()->create(['name' => 'Novels']);

        $this->get(route('admin.categories.show', $sub->category))
            ->assertOk()
            ->assertSee($sub->category->name)
            ->assertSee('Novels');
    }

    public function test_edit_shows_prefilled_form(): void
    {
        $category = Category::factory()->create(['name' => 'Books']);

        $this->get(route('admin.categories.edit', $category))
            ->assertOk()
            ->assertSee('value="Books"', false);
    }

    public function test_update_changes_category(): void
    {
        $category = Category::factory()->create(['name' => 'Books']);

        $this->put(route('admin.categories.update', $category), ['name' => 'Magazines'])
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'Magazines', 'slug' => 'books']);
    }

    public function test_update_allows_keeping_the_same_name(): void
    {
        $category = Category::factory()->create(['name' => 'Books']);

        $this->put(route('admin.categories.update', $category), ['name' => 'Books', 'description' => 'New text'])
            ->assertSessionHasNoErrors();

        $this->assertSame('New text', $category->fresh()->description);
    }

    public function test_update_rejects_name_used_by_another_category(): void
    {
        Category::factory()->create(['name' => 'Books']);
        $other = Category::factory()->create(['name' => 'Toys']);

        $this->put(route('admin.categories.update', $other), ['name' => 'Books'])->assertSessionHasErrors('name');
    }

    public function test_destroy_deletes_category_and_everything_inside(): void
    {
        $sub = Subcategory::factory()->create();
        Product::factory()->create(['subcategory_id' => $sub->id]);

        $this->delete(route('admin.categories.destroy', $sub->category))
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseCount('categories', 0);
        $this->assertDatabaseCount('subcategories', 0);
        $this->assertDatabaseCount('products', 0);
    }

    public function test_unknown_category_returns_404(): void
    {
        $this->get(route('admin.categories.edit', 999))->assertNotFound();
    }
}
