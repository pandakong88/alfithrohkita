<?php

namespace App\Imports;

use App\Models\Santri;
use App\Models\SantriSnapshot;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;

class SantriSnapshotImport implements ToCollection
{
    protected $snapshotDate;

    public function __construct($snapshotDate)
    {
        $this->snapshotDate = $snapshotDate;
    }

    public function collection(Collection $rows)
    {
        $pondokId = auth()->user()->pondok_id;

        foreach ($rows->skip(1) as $row) {

            $santri = Santri::where('pondok_id', $pondokId)
                ->where('nis', $row[0])
                ->first();

            if (!$santri) {
                continue; // skip kalau tidak ditemukan
            }

            SantriSnapshot::updateOrCreate(
                [
                    'pondok_id' => $pondokId,
                    'santri_id' => $santri->id,
                    'snapshot_date' => $this->snapshotDate,
                ],
                [
                    'status' => $this->normalizeStatus($row[3] ?? 'active'),
                    'kelas'  => $row[2] ?? null,
                    'catatan'=> $row[3] ?? null,
                    'created_by' => auth()->id(),
                ]
            );
        }
    }
}