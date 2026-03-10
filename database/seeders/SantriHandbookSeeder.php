<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Pondok; // Pastikan model ini ada
use App\Models\User;
use Illuminate\Support\Str;

class SantriHandbookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil ID pertama dari tabel pondok dan users sebagai referensi
        $pondokId = DB::table('pondoks')->first()?->id ?? 1;
        $userId = DB::table('users')->first()?->id ?? 1;

        $handbooks = [
            [
                'pondok_id'    => $pondokId,
                'version'      => '26.2.0',
                'release_date' => '2026-02-28',
                'status'       => 'published',
                'description'  => 'Penambahan beberapa poin peraturan hasil rapat temun wali santri dengan pengurus pondok pada Februari 2026.',
                'file_path'    => 'handbooks/7VDBy18ABSmJ2wm46pFPKquYZSv6bPK0o3qEXKks.pdf',
                'created_by'   => $userId,
                'created_at'   => '2026-03-10 06:15:30',
                'updated_at'   => '2026-03-10 06:16:15',
            ],
            [
                'pondok_id'    => $pondokId,
                'version'      => '26.1.0',
                'release_date' => '2024-06-15',
                'status'       => 'draft',
                'description'  => 'Versi ini masih dalam tahap pengembangan dan belum dirilis secara resmi',
                'file_path'    => 'uploads/handbooks/v1.1-draft.pdf',
                'created_by'   => $userId,
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
           
        ];

        DB::table('santri_handbooks')->insert($handbooks);
    }
}