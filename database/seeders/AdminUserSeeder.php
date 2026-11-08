<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Creates the administrator account (credentials come from config/shop.php,
 * i.e. ADMIN_NAME / ADMIN_EMAIL / ADMIN_PASSWORD in .env). Idempotent.
 */
class AdminUserSeeder extends Seeder
{
    /** Create or update the admin account from config/shop.php (ADMIN_* in .env). */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => config('shop.admin.email')],
            [
                'name' => config('shop.admin.name'),
                'password' => config('shop.admin.password'),
            ]
        );
    }
}
