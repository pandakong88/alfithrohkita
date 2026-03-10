<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SantriSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = DB::table('users')->where('email', 'admin.fitroh@gmail.com')->value('id');
        $waliIds = DB::table('walis')->where('pondok_id', 3)->pluck('id')->toArray();
        $names = ['Ahmad Zaki Fuadi', 'Siti Maryam Zahra', 'Muhammad Rifqi', 'Aisyah Humaira', 'Fathur Rahman', 'Zahra Amira', 'Ali Murtadho', 'Fatimah Az-Zahra', 'Yusuf Mansur', 'Hana Shofia', 'Ibrahim Khalil', 'Salsabila Putri', 'Umar bin Khattab', 'Annisa Rahmawati', 'Hamzah Fansuri', 'Luthfia Nisa', 'Zaidan Akbar', 'Kayla Nafisa', 'Lukman Hakim', 'Naila Farhana', 'Hasan Basri', 'Balqis Khairunnisa', 'Ridho Ilahi', 'Meisya Adinda', 'Fikri Haikal', 'Dinda Permata', 'Fauzan Azima', 'Rania az-Zahra', 'Salman Al-Farisi', 'Sabrina Aulia', 'Maulana Malik', 'Keysha Shafa', 'Taufiq Hidayat', 'Rizka Amalia', 'Akbar Maulana', 'Zahira Putri', 'Aditya Pratama', 'Najwa Shihab', 'Baim Wong', 'Indah Cahyani', 'Reza Rahadian', 'Tiara Andini', 'Gading Marten', 'Lyodra Ginting', 'Raffi Ahmad', 'Nagita Slavina', 'Atta Halilintar', 'Aurel Hermansyah', 'Bintang Emon', 'Kiky Saputri'];

        foreach ($names as $i => $name) {
            DB::table('santris')->insert([
                'pondok_id' => 3, 'wali_id' => $waliIds[array_rand($waliIds)], 'nis' => '2026' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'nama_lengkap' => $name, 'jenis_kelamin' => ($i % 2 == 0 ? 'L' : 'P'), 'status' => 'active',
                'created_by' => $adminId, 'created_at' => now(), 'updated_at' => now()
            ]);
        }
    }
}