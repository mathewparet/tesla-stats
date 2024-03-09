<?php

namespace App\Jobs;

use App\Models\Vehicle;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

/**
 * @method static \Illuminate\Foundation\Bus\PendingDispatch  dispatch(\App\Models\Vehicle $vehicle)
 */
class ImportChargesForVehicle implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private Vehicle $vehicle)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        /**
         * @var ?\Carbon\Carbon $from
         */
        $from = optional($this->vehicle->charges()->latest('id')->first())->ended_at;

        $from = $from ? $from->addSeconds() : null;

        ImportChargesForVehicleForDuration::dispatch($this->vehicle, $from);
    }
}
