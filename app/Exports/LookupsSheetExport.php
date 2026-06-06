<?php

namespace App\Exports;

use App\Models\Kelas;
use App\Models\Komplek;
use App\Models\Kamar;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class LookupsSheetExport implements FromArray, WithHeadings, WithTitle
{
    protected int $pondokId;

    public function __construct(int $pondokId)
    {
        $this->pondokId = $pondokId;
    }

    public function title(): string
    {
        return 'Lookups';
    }

    public function headings(): array
    {
        return [
            'Kelas',
            'Komplek',
            'Kamar'
        ];
    }

    public function array(): array
    {
        $kelas = Kelas::where('pondok_id', $this->pondokId)->orderBy('nama')->pluck('nama')->toArray();
        $kompleks = Komplek::where('pondok_id', $this->pondokId)->orderBy('nama')->pluck('nama')->toArray();
        $kamars = Kamar::where('pondok_id', $this->pondokId)->orderBy('nama')->pluck('nama')->toArray();

        $max = max(count($kelas), count($kompleks), count($kamars));
        $rows = [];

        for ($i = 0; $i < $max; $i++) {
            $rows[] = [
                $kelas[$i] ?? '',
                $kompleks[$i] ?? '',
                $kamars[$i] ?? '',
            ];
        }

        return $rows;
    }
}
