<?php

namespace App\Domains\Import\Actions;

use App\Models\ImportBatch;
use Illuminate\Support\Facades\DB;

class RollbackImportAction
{
    public function execute($batchId)
    {
        $batch = ImportBatch::with('changes')->findOrFail($batchId);

        DB::transaction(function () use ($batch) {

            foreach ($batch->changes()->latest()->get() as $change) {

                $model = $this->resolveModel($change->entity);

                if (!$model) {
                    continue;
                }

                $record = $model::find($change->entity_id);

                if (!$record) {
                    continue;
                }

                $record->update([
                    $change->column_name => $change->old_value
                ]);
            }

            $batch->update([
                'status' => 'rolled_back'
            ]);
        });
    }

    private function resolveModel($entity)
    {
        return match($entity) {

            'santri' => \App\Models\Santri::class,
            'wali' => \App\Models\Wali::class,
            'kamar' => \App\Models\Kamar::class,
            'lemari' => \App\Models\Lemari::class,
            'lemari_slot' => \App\Models\LemariSlot::class,

            default => null
        };
    }
}
