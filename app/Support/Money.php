<?php

namespace App\Support;

/**
 * Tiny helper that formats prices consistently across all views.
 */
class Money
{
    /** Format a number as "৳ 1,250.00" using the configured currency symbol. */
    public static function format(float|int|string|null $amount): string
    {
        return config('shop.currency').' '.number_format((float) $amount, 2);
    }
}
