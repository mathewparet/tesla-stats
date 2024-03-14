<?php

namespace App\Listeners;

use App\Events\BillingProfileMappingChanged;
use App\Jobs\ImportChargesForVehicleForDuration;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class TriggerChargeImport
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(BillingProfileMappingChanged $event): void
    {
        ImportChargesForVehicleForDuration::dispatch($event->vehicle);
    }
}
