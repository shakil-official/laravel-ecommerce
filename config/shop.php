<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Storefront / catalogue settings
    |--------------------------------------------------------------------------
    */

    // Symbol shown in front of every price (override with SHOP_CURRENCY in .env).
    'currency' => env('SHOP_CURRENCY', '৳'),

    // Number of rows per page on the admin tables.
    'admin_per_page' => (int) env('SHOP_ADMIN_PER_PAGE', 10),

    // Number of subcategory sections per page on the public product listing.
    'listing_per_page' => (int) env('SHOP_LISTING_PER_PAGE', 4),

    // Administrator account created by AdminUserSeeder (override in .env).
    'admin' => [
        'name' => env('ADMIN_NAME', 'Administrator'),
        'email' => env('ADMIN_EMAIL', 'admin@example.com'),
        'password' => env('ADMIN_PASSWORD', 'password'),
    ],

    // Image upload rules (kilobytes / allowed extensions).
    'image' => [
        'max_kb' => 2048,
        'mimes' => ['jpg', 'jpeg', 'png', 'webp'],
    ],

];
