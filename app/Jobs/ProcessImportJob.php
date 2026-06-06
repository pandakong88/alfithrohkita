<?php

namespace App\Jobs;

use App\Domains\Import\Actions\CommitImportAction;
use App\Models\ImportBatch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $batchId;
    protected int $userId;

    public function __construct(int $batchId, int $userId)
    {
        $this->batchId = $batchId;
        $this->userId = $userId;
    }

    public function handle(): void
    {
        $batch = ImportBatch::find($this->batchId);
        if (!$batch || $batch->status === 'committed') {
            return;
        }

        try {
            // Authenticate the user so auth()->id() works correctly in the Commit action
            auth()->loginUsingId($this->userId);

            app(CommitImportAction::class)->execute($this->batchId);
        } catch (\Exception $e) {
            Log::error("Failed to process background import commit for batch {$this->batchId}: " . $e->getMessage(), [
                'exception' => $e
            ]);

            $batch->update([
                'status' => 'failed'
            ]);
        }
    }
}
