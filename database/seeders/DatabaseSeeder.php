<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\RolesSeeder;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\ShopItemsTableSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run()
    {
        $this->call([
            RolesSeeder::class,
            AdminUserSeeder::class,
            ShopItemsTableSeeder::class
        ]);
    }
}