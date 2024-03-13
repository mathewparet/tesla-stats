<?php

namespace App\Jobs;

use Carbon\Carbon;
use App\Models\Charge;
use App\Models\Vehicle;
use Illuminate\Support\Arr;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
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
        return $teslaAPIServiceManager->provider($this->getBillingProfile()->team->teslaAccount->provider)
                ->timezone($this->getBillingProfile()->timezone)
                ->location($this->getBillingProfile()->latitude, $this->getBillingProfile()->longitude, $this->getBillingProfile()->radius)
                ->useAccount($this->getBillingProfile()->team->teslaAccount->config)
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
            'billing_profile' => $this->getBillingProfile()->id,
            'from' => $this->from,
            'to' => $this->to,
            'latitude' => $this->getBillingProfile()->latitude,
            'longitude' => $this->getBillingProfile()->longitude,
            'radius' => $this->getBillingProfile()->radius,
        ];
    }

    private function getBillingProfile()
    {
        return $this->vehicle->billingProfile;
    }
}
