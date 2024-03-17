<?php

namespace App\Jobs;

use Carbon\Carbon;
use App\Models\Charge;
use App\Models\Vehicle;
use Illuminate\Support\Arr;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use App\Models\BillingProfile;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use App\Contracts\TeslaAPIServiceManager;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

/**
 * @method static \Illuminate\Foundation\Bus\PendingDispatch  dispatch(\App\Models\Vehicle $vehicle, ?\Carbon\Carbon $from = null, ?\Carbon\Carbon $to = null)
 */
class ImportChargesForVehicleForDuration implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private Vehicle $vehicle, private ?Carbon $from = null, private ?Carbon $to = null)
    {
        $this->to = $to ?? now();
    }

    private function getCharges(TeslaAPIServiceManager $teslaAPIServiceManager)
    {
        return $teslaAPIServiceManager->provider($this->vehicle->team->teslaAccount->provider)
                ->useAccount($this->vehicle->team->teslaAccount->config)
                ->getCharges($this->vehicle->vin, $this->from, $this->to);
    }

    /**
     * Execute the job.
     */
    public function handle(TeslaAPIServiceManager $teslaAPIServiceManager): void
    {
        if ($this->batch() && $this->batch()->cancelled()) return;

        $vehicle_identifier = $this->generateVehicleIdentifierForLogging();

        Log::info('Importing charges for vehicle', $vehicle_identifier);

        $charges = $this->getCharges($teslaAPIServiceManager);

        Log::info('Charges retrieved: '. $charges->count());

        Log::info('Adding charges to database for', $vehicle_identifier);

        $charges->sortBy('started_at')->each(
            fn($charge) => $this->vehicle
                                ->charges()
                                ->updateOrCreate(
                                    Arr::only($charge, ['started_at', 'ended_at']), 
                                    Arr::except($charge, ['started_at', 'ended_at'])
                                )
        );

        Log::info('importing charges completed for', $vehicle_identifier);
    }

    private function generateVehicleIdentifierForLogging()
    {
        return [
            'vehicle'=>$this->vehicle->id, 
            'from' => $this->from,
            'to' => $this->to,
        ];
    }

    private function getBillingProfile()
    {
        // return $this->billingProfile;
    }
}
