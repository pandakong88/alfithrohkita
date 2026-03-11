<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;

class SantriTemplateExport implements WithHeadings
{
    public function headings(): array
    {
        return [

            'nis',
            'nama_lengkap',
            'jenis_kelamin',
            'kelas',
            'komplek',
            'kamar',
            'alamat',
            'wali_nama',
            'wali_no_hp'

        ];
    }
}