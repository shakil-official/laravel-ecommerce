<?php

namespace App\Models;

use App\Models\Concerns\HasUniqueSlug;
use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory, HasUniqueSlug;

    protected $fillable = ['name', 'slug', 'description'];

    /** Model lifecycle hooks (slug, cascades, generated numbers). */
    protected static function booted(): void
    {
        // Database-level cascades do not fire Eloquent events, so delete the
        // children through Eloquent first. That lets Product remove its image
        // file from disk. The FK cascade stays as a safety net.
        static::deleting(function (Category $category) {
            $category->subcategories()->chunkById(100, fn ($subcategories) => $subcategories->each->delete());
        });
    }

    /** Subcategories that belong to this category. */
    public function subcategories(): HasMany
    {
        return $this->hasMany(Subcategory::class);
    }

    /** All products of this category. */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
