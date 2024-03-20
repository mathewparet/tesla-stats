<?php

namespace App\Jobs;

use Carbon\Carbon;
use App\Models\Bill;
use App\Models\Charge;
use App\Models\Vehicle;
use Illuminate\Bus\Batch;
use Illuminate\Bus\Queueable;
use App\Models\BillingProfile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
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
    public function handle(): void
    {
        Log::info("Starting job");

        $billing_profiles = tap(BillingProfile::get(), fn($profiles) => Log::info('Found '. $profiles->count() . ' billing profiles'));

        $jobs = $billing_profiles
                    ->filter(fn($profile) => $profile->vehicles()->count())
                    ->map(fn($profile) => new GenerateBillsForBillingProfile($profile));

        $batch = Bus::batch($jobs)
                    ->before(fn(Batch $batch) => Log::info('Starting Bill Generation Batch', ['batch_id' => $batch->id]))
                    ->progress(fn(Batch $batch) => Log::info('Bill Generation Progress', ['batch_id' => $batch->id, 'processed' => $batch->processedJobs(), 'pending' => $batch->pendingJobs, 'total' => $batch->totalJobs]))
                    ->then(fn(Batch $batch) => Log::info('Bill Generation Batch completed successfully.', ['batch_id' => $batch->id, 'processed' => $batch->processedJobs(), 'total' => $batch->totalJobs, 'failed' => $batch->failedJobs]))
                    ->name('Bill Generation')
                    ->dispatch();

        Log::info('Ending Job');
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
