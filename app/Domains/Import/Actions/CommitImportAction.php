<?php

namespace App\Domains\Import\Actions;

use App\Models\ImportBatch;
use App\Models\ImportChange;
use Illuminate\Support\Facades\DB;
use App\Domains\Import\ResolverManager;

class CommitImportAction
{
    public function execute($batchId)
    {
        $batch = ImportBatch::with('rows')->findOrFail($batchId);
        $resolverManager = new ResolverManager();

        DB::transaction(function () use ($batch, $resolverManager) {
            
            $priorityEntities = ['komplek', 'kamar', 'lemari', 'lemari_slot', 'kelas', 'wali', 'santri'];

            foreach ($batch->rows()->where('is_valid', true)->get() as $row) {
                $payload = $row->payload;
                if ($row->mode === 'skip') continue;

                $resolved = [];

                foreach ($priorityEntities as $entity) {
                    $resolver = $resolverManager->get($entity);
                    if (!$resolver) continue;

                    // Logika Parent ID
                    $parentId = null;
                    if ($entity === 'kamar') $parentId = $resolved['komplek']->id ?? null;
                    if ($entity === 'lemari') $parentId = $resolved['kamar']->id ?? null;
                    if ($entity === 'lemari_slot') $parentId = $resolved['lemari']->id ?? null;

                    // Panggil resolver
                    $model = $this->callResolve($resolver, $batch->pondok_id, $parentId, $payload);
                    if (!$model) continue;

                    // Simpan state sebelum update untuk log audit
                    $original = $model->exists ? $model->getOriginal() : [];
                    
                    // Update & Save (Logic save ada di dalam resolver masing-masing)
                    $model = $resolver->update($model, $payload);

                    // Khusus Santri: Hubungkan relasi ke model yang sudah punya ID
                    if ($entity === 'santri') {
                        if (isset($resolved['kamar']) && $model->kamar_id != $resolved['kamar']->id) {
                            $model->kamar_id = $resolved['kamar']->id;
                        }
                        if (isset($resolved['kelas']) && $model->kelas_id != $resolved['kelas']->id) {
                            $model->kelas_id = $resolved['kelas']->id;
                        }
                        if (isset($resolved['wali']) && $model->wali_id != $resolved['wali']->id) {
                            $model->wali_id = $resolved['wali']->id;
                        }
                        if ($model->isDirty() || !$model->exists) {
                            $model->save();
                        }
                    }

                    $this->logModelChanges($batch->id, $row->id, $entity, $model, $original);
                    $resolved[$entity] = $model;
                }
            }

            $batch->update([
                'status' => 'committed',
                'committed_by' => auth()->id(),
                'committed_at' => now()
            ]);
        });
    }

    private function callResolve($resolver, $pondokId, $parentId, $payload)
    {
        $method = new \ReflectionMethod($resolver, 'resolve');
        return ($method->getNumberOfParameters() === 3) 
            ? $resolver->resolve($pondokId, $parentId, $payload)
            : $resolver->resolve($pondokId, $payload);
    }

    private function logModelChanges($batchId, $rowId, $entity, $model, $original)
    {
        if (empty($original)) {
            ImportChange::create([
                'batch_id'    => $batchId,
                'row_id'      => $rowId,
                'entity'      => $entity,
                'entity_id'   => $model->id,
                'column_name' => '__created__',
                'old_value'   => null,
                'new_value'   => 'true'
            ]);
        }

        foreach ($model->getAttributes() as $column => $newValue) {
            if (isset($original[$column]) && $original[$column] == $newValue) continue;
            if (in_array($column, ['created_at','updated_at'])) continue;

            $oldValue = $original[$column] ?? null;

            // Jika berupa DateTime/Carbon, format sebagai datetime string database
            if ($oldValue instanceof \DateTimeInterface) {
                $oldValue = $oldValue->format('Y-m-d H:i:s');
            } elseif (is_array($oldValue) || is_object($oldValue)) {
                $oldValue = json_encode($oldValue);
            }

            if ($newValue instanceof \DateTimeInterface) {
                $newValue = $newValue->format('Y-m-d H:i:s');
            } elseif (is_array($newValue) || is_object($newValue)) {
                $newValue = json_encode($newValue);
            }

            ImportChange::create([
                'batch_id'    => $batchId,
                'row_id'      => $rowId,
                'entity'      => $entity,
                'entity_id'   => $model->id,
                'column_name' => $column,
                'old_value'   => $oldValue,
                'new_value'   => $newValue
            ]);
        }
    }
}