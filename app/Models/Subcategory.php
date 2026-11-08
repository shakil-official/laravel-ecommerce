<?php

namespace App\Models;

use App\Models\Concerns\HasUniqueSlug;
use Database\Factories\SubcategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subcategory extends Model
{
    /** @use HasFactory<SubcategoryFactory> */
    use HasFactory, HasUniqueSlug;

    protected $fillable = ['category_id', 'name', 'slug', 'description'];

    /** Model lifecycle hooks (slug, cascades, generated numbers). */
    protected static function booted(): void
    {
        static::deleting(function (Subcategory $subcategory) {
            $subcategory->products()->chunkById(100, fn ($products) => $products->each->delete());
        });
    }

    /** Parent category. */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** Products that belong to this subcategory. */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
