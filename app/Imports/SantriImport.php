<?php

namespace App\Imports;

use App\Models\Santri;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Queue\ShouldQueue; 
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class SantriImport implements ToCollection,ShouldQueue,WithChunkReading
{
    protected $pondokId;
    protected $userId;

    public function __construct($pondokId, $userId)
    {
        $this->pondokId = $pondokId;
        $this->userId   = $userId;
    }

    public function collection(Collection $rows)
    {
        $data = [];

        foreach ($rows->skip(1) as $row) {

            $data[] = [
                'pondok_id'     => $this->pondokId,
                'nis'           => $row[0],
                'nama_lengkap'  => $row[1],
                'jenis_kelamin' => $row[2],
                'status'        => $row[3] ?? 'active',
                'created_by'    => $this->userId,
                'updated_by'    => $this->userId,
                'created_at'    => now(),
                'updated_at'    => now(),
            ];
        }

        Santri::upsert(
            $data,
            ['pondok_id', 'nis'],
            [
                'nama_lengkap',
                'jenis_kelamin',
                'status',
                'updated_by',
                'updated_at'
            ]
        );
    }

    private function normalizeStatus($status)
    {
        $status = strtolower(trim($status));

            return match ($status) {
                'active' => 'active',
                'aktif' => 'active',

                'inactive' => 'nonaktif',
                'nonaktif' => 'nonaktif',
                'non aktif' => 'nonaktif',
                'inaktif' => 'nonaktif',

                'lulus' => 'lulus',
                'keluar' => 'keluar',

                default => 'active',
        };
    }

    public function chunkSize(): int
    {
        return 500;
    }
}