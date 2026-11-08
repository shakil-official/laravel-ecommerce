<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

/**
 * Gives a model a URL-friendly, database-unique "slug" column.
 *
 *  "Samsung Galaxy S24"  -> samsung-galaxy-s24
 *  "Samsung Galaxy S24"  -> samsung-galaxy-s24-2   (when the first one is taken)
 *
 * The slug is generated once, on create (unless one was supplied explicitly), and
 * is then kept stable: renaming a record does NOT change its URL, so existing
 * links, bookmarks and search-engine results keep working.
 */
trait HasUniqueSlug
{
    /**
     * Build a slug for $value that no other row (except $ignoreId) is using.
     */
    public static function uniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value);

        if ($base === '') {
            $base = 'item';
        }

        $slug = $base;
        $counter = 2;

        while (static::slugExists($slug, $ignoreId)) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }

    /** True when another row already uses $slug. */
    protected static function slugExists(string $slug, ?int $ignoreId): bool
    {
        return static::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists();
    }

    /**
     * Create the slug when the record has none yet (it is never regenerated).
     */
    public static function bootHasUniqueSlug(): void
    {
        static::saving(function ($model) {
            if (empty($model->slug)) {
                $model->slug = static::uniqueSlug(
                    (string) $model->name,
                    $model->exists ? $model->getKey() : null
                );
            }
        });
    }
}
