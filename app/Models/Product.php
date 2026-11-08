<?php

namespace App\Models;

use App\Models\Concerns\HasUniqueSlug;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory, HasUniqueSlug;

    protected $fillable = [
        'category_id',
        'subcategory_id',
        'name',
        'slug',
        'description',
        'image',
        'old_price',
        'new_price',
    ];

    /** Prices are always handled as 2-decimal values. */
    protected function casts(): array
    {
        return [
            'old_price' => 'decimal:2',
            'new_price' => 'decimal:2',
        ];
    }

    /** Model lifecycle hooks (slug, cascades, generated numbers). */
    protected static function booted(): void
    {
        // Remove the uploaded image from disk when the product is deleted.
        // Inside a transaction the file is removed only after the commit, so a
        // rolled-back delete never leaves a product without its image.
        static::deleted(function (Product $product) {
            DB::afterCommit(fn () => $product->deleteImageFile());
        });
    }

    /** Category of the product. */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** Subcategory of the product. */
    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(Subcategory::class);
    }

    /**
     * Public URL of the product image, or the placeholder when the product has none.
     * No disk lookup per product (lists stay fast); a file that has gone missing is
     * replaced by the placeholder in the browser (see partials/assets.blade.php).
     */
    public function getImageUrlAttribute(): string
    {
        if ($this->image) {
            return Storage::disk('public')->url($this->image);
        }

        return asset('images/placeholder.svg');
    }

    /** True when the current price is lower than the old price. */
    public function getHasDiscountAttribute(): bool
    {
        return (float) $this->new_price < (float) $this->old_price;
    }

    /** Whole-number discount percentage (0 when there is no discount). */
    public function getDiscountPercentAttribute(): int
    {
        $old = (float) $this->old_price;

        if ($old <= 0 || ! $this->has_discount) {
            return 0;
        }

        return (int) round((($old - (float) $this->new_price) / $old) * 100);
    }

    /** Remove the stored image from the public disk (called when the product is deleted). */
    public function deleteImageFile(): void
    {
        if ($this->image) {
            Storage::disk('public')->delete($this->image);
        }
    }
}
