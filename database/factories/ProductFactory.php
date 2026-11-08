<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Subcategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /** Random product whose new price is never above the old price. */
    public function definition(): array
    {
        $old = fake()->randomFloat(2, 500, 5000);

        return [
            // category_id is always taken from the subcategory (see configure()).
            'subcategory_id' => Subcategory::factory(),
            'category_id' => null,
            'name' => fake()->unique()->words(3, true),
            'description' => fake()->paragraph(),
            'image' => null,
            'old_price' => $old,
            'new_price' => round($old * fake()->randomFloat(2, 0.6, 1), 2),
        ];
    }

    /** Make sure the subcategory / category pair stays consistent. */
    public function configure(): static
    {
        return $this->afterMaking(function (Product $product) {
            if ($product->category_id === null && $product->subcategory_id) {
                $product->category_id = Subcategory::find($product->subcategory_id)?->category_id;
            }
        });
    }

    /** A product that is not discounted. */
    public function fullPrice(): static
    {
        return $this->state(fn (array $attributes) => [
            'new_price' => $attributes['old_price'],
        ]);
    }
}
