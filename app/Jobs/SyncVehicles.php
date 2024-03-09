<?php

namespace App\Jobs;

use App\Models\TeslaAccount;
use Illuminate\Bus\Batch;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;

class SyncVehicles implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $jobs = TeslaAccount::get()->map(fn($teslaAccount) => new SyncVehiclesForAccount($teslaAccount));

        $batch = Bus::batch($jobs)
                    ->before(fn(Batch $batch) => Log::info('Starting Sync Vehicles Batch', ['batch_id' => $batch->id]))
                    ->progress(fn(Batch $batch) => Log::info('Sync Vhicles Progress', ['batch_id' => $batch->id, 'processed' => $batch->processedJobs(), 'total' => $batch->totalJobs]))
                    ->then(fn(Batch $batch) => Log::info('Sync Vehicles Job completed successfully.', ['batch_id' => $batch->id, 'processed' => $batch->processedJobs(), 'total' => $batch->totalJobs, 'failed' => $batch->failedJobs]))
                    ->name('Sync Vehicles')
                    ->dispatch();
    }
}
