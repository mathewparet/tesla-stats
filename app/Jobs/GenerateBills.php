<?php

namespace App\Jobs;

use Carbon\Carbon;
use App\Models\Bill;
use App\Models\Charge;
use App\Models\Vehicle;
use Illuminate\Bus\Queueable;
use App\Models\BillingProfile;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use App\Contracts\TeslaAPIServiceManager;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class GenerateBills implements ShouldQueue
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
        foreach(BillingProfile::active()->get() as $billingProfile)
        {
            /**
             * @var BillingProfile $billingProfile
             */
            for($from = $this->getFromDate($billingProfile), $to = $this->getToDate($billingProfile); $from->lte(now()); $from = $this->getFromDate($billingProfile), $to = $this->getToDate($billingProfile))
            {
                $account = $billingProfile->team->teslaAccount;

                $provider = $account->provider;

                $from = $this->getFromDate($billingProfile);

                $to = $this->getToDate($billingProfile);

                if($billingProfile->vehicles()->count() > 0)
                {
                    $bill = $billingProfile->bills()->save(new Bill(compact('from','to')));

                    foreach($billingProfile->vehicles as $vehicle)
                    {
                        $charges = $teslaAPIServiceManager
                                        ->provider($provider)
                                        ->useAccount($account->config)
                                        ->timezone($billingProfile->timezone)
                                        ->location($billingProfile->latitude, $billingProfile->longitude, $billingProfile->radius)
                                        ->getCharges($vehicle->vin, $from, $to);

                    }
                }
            }
        }
    }

    /**
     * Get the next bill-to date
     * 
     * @param BillingProfile $billingProfile
     * @return Carbon
     */
    private function getToDate(BillingProfile $billingProfile)
    {
        $billDateThisMonth = $this->getBillDayThisMonth($billingProfile->activated_on, $billingProfile->bill_day - 1);

        $latestBill = $this->getLatestBill($billingProfile);

        $to = $latestBill
                ? $this->getNextDay($latestBill->to, $billingProfile->bill_day -1)
                : (
                    $billDateThisMonth->lte($billingProfile->activated_on)
                        ? $this->getNextDay($billingProfile->activated_on, $billingProfile->bill_day - 1)
                        : $billDateThisMonth
                    );

        return !$billingProfile->deactivated_on 
                    ? $to
                    : (
                        $to->gt($billingProfile->deactivated_on)
                        ? $billingProfile->deactivated_on
                        : $to
                    );
    }

    /**
     * Get the full billing day for this month
     * 
     * @param Carbon $ref Reference date
     * @param int $day
     * @return Carbon
     */
    private function getBillDayThisMonth(Carbon $ref, $day)
    {
        return $ref->firstOfMonth()->addDays($day);
    }

    /**
     * Get the next bill-from date
     * 
     * @param BillingProfile $billingProfile
     * @return Carbon
     */
    private function getFromDate(BillingProfile $billingProfile)
    {
        $latestBill = $this->getLatestBill($billingProfile);

        return $latestBill
                ? $this->getNextDay($latestBill->to, $billingProfile->bill_day)
                : $billingProfile->activated_on;
    }

    /**
     * Get the next day after `$ref`
     * 
     * @param Carbon $re Reference date
     * @param int $day
     * @return Carbon
     */
    private function getNextDay(Carbon $ref, $day)
    {
        return $ref->lastOfMonth()->addDays($day);
    }

    /**
     * Get the latest bill generated for this profile
     * 
     * @param BillingProfile $billingProfile
     * @return BillingProfile|null
     */
    private function getLatestBill(BillingProfile $billingProfile)
    {
        return $billingProfile->bills()->latest()->first();
    }
}
