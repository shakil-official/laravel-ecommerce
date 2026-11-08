<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Services\PlaceholderImageGenerator;
use Illuminate\Database\Seeder;

/**
 * Seeds a demo catalogue (3 categories, 8 subcategories, 22 products).
 * It is idempotent: running it twice never creates duplicates, so it is safe
 * to run on every container start.
 */
class DemoCatalogSeeder extends Seeder
{
    /** Create the demo categories, subcategories and products with generated images. */
    public function run(PlaceholderImageGenerator $images): void
    {
        $catalog = require __DIR__.'/data/catalog.php';

        foreach ($catalog as $categoryName => $subcategories) {
            $category = Category::firstOrCreate(
                ['name' => $categoryName],
                ['description' => "Everything in {$categoryName}."]
            );

            foreach ($subcategories as $subcategoryName => $products) {
                $subcategory = $category->subcategories()->firstOrCreate(
                    ['name' => $subcategoryName],
                    ['description' => "{$subcategoryName} from our {$categoryName} range."]
                );

                foreach ($products as [$name, $description, $oldPrice, $newPrice]) {
                    if (Product::where('subcategory_id', $subcategory->id)->where('name', $name)->exists()) {
                        continue;
                    }

                    Product::create([
                        'category_id' => $category->id,
                        'subcategory_id' => $subcategory->id,
                        'name' => $name,
                        'description' => $description,
                        'image' => $images->generate($name),
                        'old_price' => $oldPrice,
                        'new_price' => $newPrice,
                    ]);
                }
            }
        }
    }
}
