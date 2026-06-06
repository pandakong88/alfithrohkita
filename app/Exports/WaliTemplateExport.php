<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;

class WaliTemplateExport implements WithHeadings
{
    public function headings(): array
    {
        return [
            'nama',
            'nik',
            'no_hp',
            'alamat',
            'pekerjaan'
        ];
    }
}
