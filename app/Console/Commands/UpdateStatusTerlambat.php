<?php

namespace App\Console\Commands;

use App\Models\Perizinan;
use Illuminate\Console\Command;

class UpdateStatusTerlambat extends Command
{
    protected $signature = 'app:update-status-terlambat';
    protected $description = 'Otomatis set status terlambat jika melewati batas kembali';

    public function handle()
    {
        $affected = Perizinan::where('status', 'aktif')
            ->where('batas_kembali', '<', now())
            ->update(['status' => 'terlambat']);

        if ($affected > 0) {
            $this->info("Berhasil mengupdate $affected perizinan ke status TERLAMBAT.");
        }
    }
}