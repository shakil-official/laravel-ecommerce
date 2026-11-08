<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /** Admin account, demo catalogue and demo orders (each seeder is idempotent). */
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            DemoCatalogSeeder::class,
            DemoOrderSeeder::class,
        ]);
    }
}
