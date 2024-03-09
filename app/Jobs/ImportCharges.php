<?php

namespace App\Jobs;

use App\Models\Vehicle;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use App\Contracts\TeslaAPIServiceManager;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

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

        foreach($billable_vehicles as $vehicle)
        {
            /**
             * @var \App\Models\Vehicle $vehicle
             */
            ImportChargesForVehicle::dispatch($vehicle);
        }

        Log::info('Ended job');
    }
}
