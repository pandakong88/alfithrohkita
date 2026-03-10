<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PondokSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('pondoks')->updateOrInsert(
            ['id' => 3],
            [
                'name' => 'Pondok Pesantren Al-Fitroh',
                'slug' => 'al-fitroh',
                'address' => 'Yogyakarta',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}