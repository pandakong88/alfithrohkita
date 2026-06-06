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
            // Group changes by entity and entity_id to restore each record in one operation or delete it
            $changesByRecord = $batch->changes()
                ->latest()
                ->get()
                ->groupBy(function ($change) {
                    return $change->entity . '_' . $change->entity_id;
                });

            foreach ($changesByRecord as $key => $recordChanges) {
                // Check if this record was newly created in this batch
                $isCreated = $recordChanges->contains('column_name', '__created__');

                $firstChange = $recordChanges->first();
                $modelClass = $this->resolveModel($firstChange->entity);

                if (!$modelClass) {
                    continue;
                }

                $record = $modelClass::find($firstChange->entity_id);
                if (!$record) {
                    continue;
                }

                if ($isCreated) {
                    // Record was created during import, so delete it during rollback
                    $record->delete();
                } else {
                    // Record existed, restore its original column values
                    $updates = [];
                    foreach ($recordChanges as $change) {
                        if ($change->column_name === '__created__') {
                            continue;
                        }

                        $val = $change->old_value;
                        if (is_string($val) && (str_starts_with($val, '{') || str_starts_with($val, '[') || str_starts_with($val, '"'))) {
                            $decoded = json_decode($val, true);
                            if (json_last_error() === JSON_ERROR_NONE) {
                                $val = $decoded;
                            }
                        }

                        $updates[$change->column_name] = $val;
                    }

                    if (!empty($updates)) {
                        $record->update($updates);
                    }
                }
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
            'kelas' => \App\Models\Kelas::class,
            'komplek' => \App\Models\Komplek::class,
            'lemari' => \App\Models\Lemari::class,
            'lemari_slot' => \App\Models\LemariSlot::class,
            default => null
        };
    }
}
