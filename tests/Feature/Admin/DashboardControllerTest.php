<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    public function test_dashboard_shows_counts_and_latest_products(): void
    {
        $product = Product::factory()->create(['name' => 'Dashboard Widget']);

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertViewHas('stats', ['categories' => 1, 'subcategories' => 1, 'products' => 1])
            ->assertSee('Dashboard Widget')
            ->assertSee($product->category->name);
    }

    public function test_dashboard_renders_with_empty_database(): void
    {
        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('No products yet.');
    }
}
