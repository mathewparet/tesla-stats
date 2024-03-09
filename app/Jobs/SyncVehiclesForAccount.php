<?php

namespace App\Jobs;

use App\Models\TeslaAccount;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use App\Contracts\TeslaAPIServiceManager;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Database\UniqueConstraintViolationException;

/**
 * @method static \Illuminate\Foundation\Bus\PendingDispatch  dispatch(\App\Models\TeslaAccount $teslaAccount)
 * @method static \Illuminate\Foundation\Bus\PendingDispatch  dispatchSync(\App\Models\TeslaAccount $teslaAccount)
 */
class SyncVehiclesForAccount implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private TeslaAccount $teslaAccount)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(TeslaAPIServiceManager $teslaAPIServiceManager): void
    {
        $vehicles =  $this->getVehiclesList($teslaAPIServiceManager);

        foreach($vehicles as $vehicle)
        {
            try
            {
                $this->teslaAccount->team->updateOrCreateVehicle($vehicle);
            }
            catch(UniqueConstraintViolationException $e)
            {
                // do nothing
            }
        }
    }

    private function getVehiclesList(TeslaAPIServiceManager $teslaAPIServiceManager)
    {
        return $teslaAPIServiceManager
                ->provider($this->teslaAccount->provider)
                ->useAccount($this->teslaAccount->config)
                ->getVehicles();
    }
}
