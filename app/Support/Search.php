<?php

namespace App\Support;

/**
 * Helpers for building safe SQL LIKE patterns from user input.
 */
final class Search
{
    /** Escape character used together with "ESCAPE '!'" in the queries. */
    public const ESCAPE = '!';

    /**
     * "50%_off" -> "%50!%!_off%": the user's % and _ are matched literally.
     * Use with: whereRaw("column like ? escape '!'", [Search::like($term)]).
     */
    public static function like(string $term): string
    {
        return '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $term).'%';
    }
}
