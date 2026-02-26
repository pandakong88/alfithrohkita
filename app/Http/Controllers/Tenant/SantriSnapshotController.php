<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Domains\Santri\Actions\ImportSantriSnapshotPreviewAction;
use App\Domains\Santri\Actions\CommitSantriSnapshotAction;
use App\Models\SantriSnapshotBatch;

class SantriSnapshotController extends Controller
{
    public function importForm()
    {
        return view('tenant.santri.snapshot-import');
    }

    public function preview(Request $request)
    {
        $request->validate([
            'snapshot_date' => 'required|date',
            'file' => 'required|mimes:xlsx,csv',
        ]);

        $batch = app(ImportSantriSnapshotPreviewAction::class)
            ->execute(
                $request->file('file'),
                $request->snapshot_date
            );

        return redirect()->route(
            'tenant.santri.snapshot.preview.show',
            $batch->id
        );
    }

    public function showPreview(SantriSnapshotBatch $batch)
    {
        $batch->load('rows');

        return view('tenant.santri.snapshot-preview', compact('batch'));
    }

    public function commit(SantriSnapshotBatch $batch)
    {
        app(CommitSantriSnapshotAction::class)
            ->execute($batch);

        return redirect()
            ->route('tenant.santri.snapshot.import')
            ->with('success', 'Snapshot berhasil di-commit.');
    }


}