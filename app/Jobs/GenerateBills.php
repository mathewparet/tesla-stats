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
        foreach(BillingProfile::get() as $billingProfile)
        {
            /**
             * @var BillingProfile $billingProfile
             */
            if($billingProfile->vehicles()->count() > 0)
            {
                for(
                    $from =  $this->getFromDate($billingProfile), $to = $this->getToDate($billingProfile); 
                    $to->endOfDay()->lte(now()), $from->lte($billingProfile->deactivated_on); 
                    $from =  $this->getFromDate($billingProfile), $to = $this->getToDate($billingProfile))
                {
                    $bill = $billingProfile->bills()->save(new Bill(compact('from','to')));
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
        $latestBill = $this->getLatestBill($billingProfile);

        $to = $latestBill
                ? $this->getNextDay($latestBill->to, $billingProfile->bill_day -1)
                :  $this->nextNthNoOverflow($billingProfile->bill_day -1, $billingProfile->activated_on);

        return (!$billingProfile->deactivated_on 
                    ? $to
                    : (
                        $to->gt($billingProfile->deactivated_on)
                        ? $billingProfile->deactivated_on
                        : $to
                    ))->shiftTimezone($billingProfile->timezone);
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

        return ($latestBill
                ? $this->nextNthNoOverflow($billingProfile->bill_day, $latestBill->to)
                : $billingProfile->activated_on)->shiftTimezone($billingProfile->timezone);
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

    private function nextNthNoOverflow(int $nth, Carbon $from)
    {
        $day_in_month = $from->copy()->setUnitNoOverflow('day', $nth, 'month');
        return $day_in_month->gt($from) ? $day_in_month : $day_in_month->addMonthNoOverflow();
    }

    /**
     * Get the latest bill generated for this profile
     * 
     * @param BillingProfile $billingProfile
     * @return BillingProfile|null
     */
    private function getLatestBill(BillingProfile $billingProfile)
    {
        return $billingProfile->bills()->latest('id')->first();
    }
}
