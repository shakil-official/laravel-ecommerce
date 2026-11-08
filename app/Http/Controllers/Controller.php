<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

abstract class Controller
{
    /** Row counts the admin may choose from in the "per page" selector. */
    public const PER_PAGE_OPTIONS = [10, 25, 50, 100];

    /**
     * Rows per page for admin tables: ?per_page= when it is an allowed value,
     * otherwise the default from config/shop.php.
     */
    protected function perPage(Request $request): int
    {
        $requested = $request->integer('per_page');

        return in_array($requested, self::PER_PAGE_OPTIONS, true)
            ? $requested
            : (int) config('shop.admin_per_page');
    }

    /**
     * Pages that contain a private token / customer data: never cache them,
     * keep them out of search engines and do not leak the URL via Referer.
     */
    protected function sensitive(\Illuminate\Http\Response $response): \Illuminate\Http\Response
    {
        return $response->withHeaders([
            'Cache-Control' => 'no-store, private',
            'X-Robots-Tag' => 'noindex, nofollow',
            'Referrer-Policy' => 'no-referrer',
        ]);
    }
}
