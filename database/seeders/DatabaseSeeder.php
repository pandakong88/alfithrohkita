<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            PondokSeeder::class,
            UserSeeder::class,
            // WaliSeeder::class,
            // SantriSeeder::class,
            SantriHandbookSeeder::class,
            ImportFieldSeeder::class,
            TemplateVariableSeeder::class,
        ]);
    }
}