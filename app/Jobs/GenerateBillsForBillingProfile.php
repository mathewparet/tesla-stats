<?php

namespace App\Jobs;

use Carbon\Carbon;
use App\Models\Bill;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use App\Models\BillingProfile;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class GenerateBillsForBillingProfile implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private BillingProfile $billingProfile)
    {
        
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Starting bill generation for ', ['billing_profile_id' => $this->billingProfile->id]);

        if ($this->batch() && $this->batch()->cancelled()) return;

        $from = $this->getFromDate(); // Initialize $from outside the loop

        while($this->isFromDateBeforeOrEqualToDeactivationDate($from)) // Use while loop instead of for loop
        {
            $to = $this->getToDate(); // Calculate $to inside the loop

            Log::debug("Generating bill for ", compact('from', 'to'));
            Log::debug("Next from date", compact('from'));

            $bill = $this->billingProfile->bills()->save(new Bill(compact('from','to')));

            $from = $this->getFromDate(); // Update $from for the next iteration
        }

        Log::info('Bill generation completed for ', ['billing_profile_id' => $this->billingProfile->id]);
    }

    private function isFromDateBeforeOrEqualToDeactivationDate($from)
    {
        return tap(
            $from->lte($this->billingProfile->deactivated_on), 
            fn($not_deactivated) 
                => Log::debug('Checking if deactivated date of '. $this->billingProfile->deactivated_on.' is before from date of '.$from, compact('not_deactivated'))
        );
    }

    /**
     * Get the next bill-to date
     * 
     * @return Carbon
     */
    private function getToDate()
    {
        $latestBill = $this->getLatestBill();

        $to = $latestBill
                ? $this->getNextDay($latestBill->to, $this->billingProfile->bill_day -1)
                :  $this->nextNthNoOverflow($this->billingProfile->bill_day -1, $this->billingProfile->activated_on);

        return (!$this->billingProfile->deactivated_on 
                    ? $to
                    : (
                        $to->gt($this->billingProfile->deactivated_on)
                        ? $this->billingProfile->deactivated_on
                        : $to
                    ))->shiftTimezone($this->billingProfile->timezone);
    }

    /**
     * Get the next bill-from date
     * 
     * @return Carbon
     */
    private function getFromDate()
    {
        $latestBill = $this->getLatestBill();

        return ($latestBill
                ? $this->nextNthNoOverflow($this->billingProfile->bill_day, $latestBill->to)
                : $this->billingProfile->activated_on)->shiftTimezone($this->billingProfile->timezone);
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
     * @return BillingProfile|null
     */
    private function getLatestBill()
    {
        return $this->billingProfile->bills()->latest('id')->first();
    }
}
