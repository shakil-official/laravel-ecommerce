<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subcategory>
 */
class SubcategoryFactory extends Factory
{
    protected $model = Subcategory::class;

    /** Random subcategory (creates its own category when none is given). */
    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'name' => fake()->unique()->words(2, true),
            'description' => fake()->sentence(),
        ];
    }
}
