<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\Product;
use App\Models\Subcategory;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_demo_catalogue(): void
    {
        Storage::fake('public');

        $this->seed(DatabaseSeeder::class);

        $this->assertSame(3, Category::count());
        $this->assertSame(8, Subcategory::count());
        $this->assertSame(22, Product::count());
        $this->assertSame(0, Product::whereColumn('new_price', '>', 'old_price')->count());
    }

    public function test_seeder_is_idempotent(): void
    {
        Storage::fake('public');

        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(3, Category::count());
        $this->assertSame(22, Product::count());
    }

    public function test_every_seeded_product_belongs_to_the_category_of_its_subcategory(): void
    {
        Storage::fake('public');
        $this->seed(DatabaseSeeder::class);

        Product::with('subcategory')->get()->each(
            fn ($p) => $this->assertSame($p->subcategory->category_id, $p->category_id)
        );
    }

    public function test_seeder_creates_admin_user_from_config(): void
    {
        Storage::fake('public');
        config(['shop.admin' => ['name' => 'Boss', 'email' => 'boss@example.com', 'password' => 'top-secret']]);

        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(1, User::count());
        $user = User::firstOrFail();
        $this->assertSame('boss@example.com', $user->email);
        $this->assertTrue(Hash::check('top-secret', $user->password));
    }

    public function test_seeder_creates_demo_orders_in_different_statuses_once(): void
    {
        Storage::fake('public');

        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(6, Order::count());
        $this->assertGreaterThanOrEqual(5, Order::distinct()->count('status'));
        $this->assertSame(0, Order::whereNull('product_id')->count());
    }

    public function test_demo_orders_have_known_tracking_tokens(): void
    {
        Storage::fake('public');
        $this->seed(DatabaseSeeder::class);

        $this->assertSame('BS-DEMO-0001', Order::where('tracking_token', 'DEMO-0001-TRK1')->value('order_number'));
        $this->assertSame(6, Order::whereNotNull('tracking_token')->count());
    }
}
