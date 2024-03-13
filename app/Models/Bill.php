<?php

namespace App\Models;

use App\Notifications\NewBillReady;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Cache;
use mathewparet\LaravelPolicyAbilitiesExport\Traits\ExportsPermissions;

class Bill extends Model
{
    use HasFactory;
    use ExportsPermissions;

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
        if($this->to->lte(now()))
        {
            $this->billingProfile->team->owner->notify(new NewBillReady($this));
        }
    }

    public function totalCost(): Attribute
    {
        return Attribute::make(
            get: fn() => Cache::remember("bills-total-cost-".$this->id, now()->addSeconds(60), fn() => $this->getCharges()->sum('cost'))
        );
    }

    public function energyConsumed(): Attribute
    {
        return Attribute::make(
            get: fn() => Cache::remember("bills-energ-consumed-".$this->id, now()->addSeconds(60), fn() => $this->getCharges()->sum('energy_consumed'))
        );
    }

    public function getCharges()
    {
        return Charge::whereIn('vehicle_id', $this->billingProfile->vehicles()->pluck('id'))
            ->where('started_at', '>=', $this->from)
            ->where('ended_at', '<', $this->to);
    }
}
