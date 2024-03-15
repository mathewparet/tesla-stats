<?php

namespace App\Models;

use App\Traits\HasHashId;
use App\Notifications\NewBillReady;
use Illuminate\Support\Facades\Log;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use mathewparet\LaravelPolicyAbilitiesExport\Traits\ExportsPermissions;

class Bill extends Model
{
    use HasFactory;
    use ExportsPermissions;
    use HasHashId;

    protected $fillable = [
        'from',
        'to',
    ];

    protected $casts = [
        'from' => 'date:Y-m-d',
        'to' => 'date:Y-m-d',
    ];

    protected $appends = [
        'total_cost',
        'energy_consumed',
    ];

    public function billingProfile()
    {
        return $this->belongsTo(BillingProfile::class);
    }

    public static function booted()
    {
        static::created(fn($bill) => $bill->sendBill());
    }

    public function sendBill()
    {
        $lastBill = $this->billingProfile->bills()->latest('id')->limit(2)->get();

        if($lastBill->count() == 2)
        {
            $this->billingProfile->team->owner->notify(new NewBillReady($lastBill[1]));
        }
    }

    public function totalCost(): Attribute
    {
        return Attribute::make(
            get: fn() => Cache::remember("bills-total-cost-".$this->id, now()->addSeconds(config('bill.summary.cache')), fn() => $this->getCharges()->sum('cost'))
        );
    }

    public function energyConsumed(): Attribute
    {
        return Attribute::make(
            get: fn() => Cache::remember("bills-energ-consumed-".$this->id, now()->addSeconds(config('bill.summary.cache')), fn() => $this->getCharges()->sum('energy_consumed'))
        );
    }

    public function getCharges()
    {
        return Charge::whereIn('vehicle_id', $this->billingProfile->vehicles()->pluck('vehicles.id'))
                    ->withinLocation($this->billingProfile->latitude, $this->billingProfile->longitude, $this->billingProfile->radius)
                    ->where('started_at', '>=', $this->from->shiftTimezone($this->billingProfile->timezone)->subDay()->startOfDay())
                    ->where('ended_at', '<', $this->to->shiftTimezone($this->billingProfile->timezone)->startOfDay());
    }
}
