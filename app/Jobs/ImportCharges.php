<?php

namespace App\Jobs;

use App\Models\Vehicle;
use Illuminate\Bus\Batch;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use App\Contracts\TeslaAPIServiceManager;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ImportCharges implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        
    }

    /**
     * Execute the job.
     */
    public function handle(TeslaAPIServiceManager $teslaAPIServiceManager): void
    {
        Log::info('Started job');

        $billable_vehicles = Vehicle::billable()->get();

        Log::info('Identified billable vehicles: ' . $billable_vehicles->count());

        Log::debug('Identified billable vehicles', compact('billable_vehicles'));

        $jobs = $billable_vehicles->map(function($vehicle) {
            $from = optional($vehicle->charges()->latest('id')->first())->ended_at;

            $from = $from ? $from->addSeconds() : null;

            return new ImportChargesForVehicleForDuration($vehicle, $from);
        });

        $batch = Bus::batch($jobs)
                    ->before(fn(Batch $batch) => Log::info('Starting Import Charges Batch', ['batch_id' => $batch->id]))
                    ->progress(fn(Batch $batch) => Log::info('Import Charges Progress', ['batch_id' => $batch->id, 'processed' => $batch->processedJobs(), 'pending' => $batch->pendingJobs, 'total' => $batch->totalJobs]))
                    ->then(fn(Batch $batch) => Log::info('Import Charges Batch completed successfully.', ['batch_id' => $batch->id, 'processed' => $batch->processedJobs(), 'total' => $batch->totalJobs, 'failed' => $batch->failedJobs]))
                    ->name('Import Charges')
                    ->dispatch();

        Log::info('Ended job');
    }
}
