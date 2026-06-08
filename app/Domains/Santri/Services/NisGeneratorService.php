<?php

namespace App\Domains\Santri\Services;

use App\Models\Pondok;
use App\Models\Santri;
use Carbon\Carbon;

class NisGeneratorService
{
    /**
     * Generate NIS berikutnya berdasarkan pola (pattern) pondok
     */
    public function generate(int $pondokId, ?string $tanggalMasuk = null): string
    {
        $pondok = Pondok::findOrFail($pondokId);
        $pattern = $pondok->nis_pattern ?: '[YEAR][SEQ:4]';

        $date = $tanggalMasuk ? Carbon::parse($tanggalMasuk) : Carbon::now();
        $year4 = $date->format('Y');
        $year2 = $date->format('y');

        // Ganti placeholder tahun
        $resolvedPattern = str_replace(
            ['[YEAR]', '[YEAR2]'],
            [$year4, $year2],
            $pattern
        );

        // Cari placeholder [SEQ:N]
        if (preg_match('/\[SEQ:(\d+)\]/', $resolvedPattern, $matches)) {
            $seqLength = (int) $matches[1];
            $seqPattern = $matches[0]; // e.g. '[SEQ:4]'

            // Pisahkan prefix dan suffix di sekitar placeholder urutan
            $patternParts = explode($seqPattern, $resolvedPattern);
            $prefix = $patternParts[0] ?? '';
            $suffix = $patternParts[1] ?? '';

            // Cari semua NIS santri di pondok ini yang memiliki kecocokan pola prefix & suffix
            $likeQuery = $prefix . '%' . $suffix;
            $existingNisList = Santri::where('pondok_id', $pondokId)
                ->where('nis', 'LIKE', $likeQuery)
                ->pluck('nis');

            $maxSeq = 0;
            foreach ($existingNisList as $existingNis) {
                $temp = $existingNis;
                
                // Potong bagian prefix
                if ($prefix !== '' && str_starts_with($temp, $prefix)) {
                    $temp = substr($temp, strlen($prefix));
                }
                
                // Potong bagian suffix
                if ($suffix !== '' && str_ends_with($temp, $suffix)) {
                    $temp = substr($temp, 0, -strlen($suffix));
                }

                // Jika sisanya numerik, ambil nilai maksimalnya
                if (is_numeric($temp)) {
                    $seqVal = (int) $temp;
                    if ($seqVal > $maxSeq) {
                        $maxSeq = $seqVal;
                    }
                }
            }

            $nextSeq = $maxSeq + 1;
            $paddedSeq = str_pad((string) $nextSeq, $seqLength, '0', STR_PAD_LEFT);

            return $prefix . $paddedSeq . $suffix;
        }

        // Fallback jika tidak ada placeholder sequence di pattern
        $lastSantri = Santri::where('pondok_id', $pondokId)->latest('id')->first();
        $nextId = $lastSantri ? ($lastSantri->id + 1) : 1;
        return $resolvedPattern . $nextId;
    }
}
