<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WaliSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = DB::table('users')->where('email', 'admin.fitroh@gmail.com')->value('id');
        $walis = [
            ['nama' => 'Abu King Kong2', 'nik' => '2123410092821232', 'no_hp' => '081324556212', 'alamat' => 'Jogjakarta2', 'pekerjaan' => 'Pengangguran2'],
            ['nama' => 'Abi Nanda', 'nik' => '21234100928221', 'no_hp' => '08132455621', 'alamat' => 'Bantul', 'pekerjaan' => 'Pemancing'],
            ['nama' => 'Siti Aminah', 'nik' => '3201012345670001', 'no_hp' => '081234567890', 'alamat' => 'Sleman', 'pekerjaan' => 'Guru'],
            ['nama' => 'Budi Santoso', 'nik' => '3201012345670002', 'no_hp' => '081234567891', 'alamat' => 'Kota Gede', 'pekerjaan' => 'Wiraswasta'],
            ['nama' => 'Agus Setiawan', 'nik' => '3201012345670003', 'no_hp' => '081234567892', 'alamat' => 'Kulon Progo', 'pekerjaan' => 'Petani'],
            ['nama' => 'Lestari Putri', 'nik' => '3201012345670004', 'no_hp' => '081234567893', 'alamat' => 'Gunung Kidul', 'pekerjaan' => 'IRT'],
            ['nama' => 'Dedi Kurniawan', 'nik' => '3201012345670005', 'no_hp' => '081234567894', 'alamat' => 'Depok', 'pekerjaan' => 'Buruh'],
            ['nama' => 'Eko Prasetyo', 'nik' => '3201012345670006', 'no_hp' => '081234567895', 'alamat' => 'Kasihan', 'pekerjaan' => 'Driver'],
            ['nama' => 'Rina Wijaya', 'nik' => '3201012345670007', 'no_hp' => '081234567896', 'alamat' => 'Mlati', 'pekerjaan' => 'Pedagang'],
            ['nama' => 'Andi Wijaya', 'nik' => '3201012345670008', 'no_hp' => '081234567897', 'alamat' => 'Prawirotaman', 'pekerjaan' => 'Karyawan'],
            ['nama' => 'Maya Sartika', 'nik' => '3201012345670009', 'no_hp' => '081234567898', 'alamat' => 'Sewon', 'pekerjaan' => 'Bidan'],
            ['nama' => 'Hendra Gunawan', 'nik' => '3201012345670010', 'no_hp' => '081234567899', 'alamat' => 'Wates', 'pekerjaan' => 'PNS'],
            ['nama' => 'Dewi Lestari', 'nik' => '3201012345670011', 'no_hp' => '081234567800', 'alamat' => 'Imogiri', 'pekerjaan' => 'Pengrajin'],
            ['nama' => 'Fajar Nugraha', 'nik' => '3201012345670012', 'no_hp' => '081234567801', 'alamat' => 'Kalasan', 'pekerjaan' => 'Arsitek'],
            ['nama' => 'Sari Indah', 'nik' => '3201012345670013', 'no_hp' => '081234567802', 'alamat' => 'Gamping', 'pekerjaan' => 'Penjahit'],
            ['nama' => 'Bambang Tri', 'nik' => '3201012345670014', 'no_hp' => '081234567803', 'alamat' => 'Sentolo', 'pekerjaan' => 'Mekanik'],
            ['nama' => 'Ratna Sari', 'nik' => '3201012345670015', 'no_hp' => '081234567804', 'alamat' => 'Tegalrejo', 'pekerjaan' => 'Apoteker'],
        ];

        foreach ($walis as $w) {
            DB::table('walis')->insert([
                'pondok_id' => 3, 'nama' => $w['nama'], 'nik' => $w['nik'], 'no_hp' => $w['no_hp'],
                'alamat' => $w['alamat'], 'pekerjaan' => $w['pekerjaan'], 'created_by' => $adminId, 'created_at' => now(), 'updated_at' => now()
            ]);
        }
    }
}