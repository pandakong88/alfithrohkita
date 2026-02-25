<?php

namespace App\Domains\Wali\Actions;

use App\Models\Wali;
use App\Domains\Shared\Actions\LogActivityAction;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeleteWaliAction
{
    public function __construct(
        protected LogActivityAction $logActivity
    ) {}

    public function execute(Wali $wali): void
    {
        DB::transaction(function () use ($wali) {

            $hasActiveSantri = $wali->santris()
                ->where('status', 'active')
                ->exists();

            if ($hasActiveSantri) {
                throw ValidationException::withMessages([
                    'wali' => 'Tidak bisa menghapus wali yang masih memiliki santri aktif.'
                ]);
            }

            $oldValues = $wali->toArray();

            $wali->delete();

            $this->logActivity->execute(
                event: 'wali.deleted',
                subject: $wali,
                description: 'Menghapus wali',
                oldValues: $oldValues,
                newValues: null
            );
        });
    }
}