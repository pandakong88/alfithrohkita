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
            
            // Definisikan urutan hierarki secara manual agar aman
            // Urutan: Komplek -> Kamar -> Lemari -> Kelas -> Wali -> Santri
            $priorityEntities = ['komplek', 'kamar', 'lemari', 'lemari_slot', 'kelas', 'wali', 'santri'];

            foreach ($batch->rows()->where('is_valid', true)->get() as $row) {
                $payload = $row->payload;
                if ($row->mode === 'skip') continue;

                $resolved = [];

                foreach ($priorityEntities as $entity) {
                    $resolver = $resolverManager->get($entity);
                    if (!$resolver) continue;

                    // Logika penentuan Parent ID secara dinamis
                    $parentId = null;
                    if ($entity === 'kamar') $parentId = $resolved['komplek']->id ?? null;
                    if ($entity === 'lemari') $parentId = $resolved['kamar']->id ?? null;
                    if ($entity === 'lemari_slot') $parentId = $resolved['lemari']->id ?? null;

                    // Panggil resolve dengan jumlah parameter yang fleksibel
                    $model = $this->callResolve($resolver, $batch->pondok_id, $parentId, $payload);

                    if (!$model) continue;

                    $original = $model->getOriginal();
                    
                    // Update data model
                    $resolver->update($model, $payload);

                    // Khusus Santri: Hubungkan ke relasi yang sudah ditemukan di baris yang sama
                    if ($entity === 'santri') {
                        if (isset($resolved['kamar'])) $model->kamar_id = $resolved['kamar']->id;
                        if (isset($resolved['kelas'])) $model->kelas_id = $resolved['kelas']->id;
                        if (isset($resolved['wali'])) $model->wali_id = $resolved['wali']->id;
                        $model->save();
                    }

                    $model->refresh();
                    $this->logModelChanges($batch->id, $row->id, $entity, $model, $original);
                    
                    // Simpan hasil resolve untuk digunakan entitas berikutnya di loop ini
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

    /**
     * Helper untuk memanggil method resolve secara dinamis
     * Berfungsi menangani perbedaan jumlah parameter antar Resolver
     */
    private function callResolve($resolver, $pondokId, $parentId, $payload)
    {
        $method = new \ReflectionMethod($resolver, 'resolve');
        $paramsCount = $method->getNumberOfParameters();

        // Jika resolver butuh 3 parameter (pondok_id, parent_id, payload)
        // Contoh: KamarResolver, LemariResolver
        if ($paramsCount === 3) {
            return $resolver->resolve($pondokId, $parentId, $payload);
        }

        // Jika resolver butuh 2 parameter (pondok_id, payload)
        // Contoh: SantriResolver, WaliResolver, KomplekResolver
        return $resolver->resolve($pondokId, $payload);
    }

    private function logModelChanges($batchId, $rowId, $entity, $model, $original)
    {
        foreach ($model->getAttributes() as $column => $newValue) {
            if (!array_key_exists($column, $original)) continue;
            if (in_array($column, ['created_at','updated_at'])) continue;

            $oldValue = $original[$column];
            if ($oldValue == $newValue) continue;

            ImportChange::create([
                'batch_id' => $batchId,
                'row_id' => $rowId,
                'entity' => $entity,
                'entity_id' => $model->id,
                'column_name' => $column,
                'old_value' => $oldValue,
                'new_value' => $newValue
            ]);
        }
    }
}