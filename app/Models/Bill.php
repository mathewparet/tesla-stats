<?php

namespace App\Models;

use App\Notifications\NewBillReady;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
        'from' => 'date:Y-md-d',
        'to' => 'date:Y-md-d',
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

    public function energyUsed(): Attribute
    {
        return new Attribute(
            get: fn() => $this->getCharges()->sum('energy_consumed')
        );
    }

    public function totalCost(): Attribute
    {
        return new Attribute(
            get: fn() => $this->getCharges()->sum('cost')
        );
    }

    private function getCharges()
    {
        $query = Charge::whereIn('vehicle_id', $this->billingProfile->vehicles()->pluck('id'))
            ->where('started_at', '>=', $this->from->setTimezone($this->billingProfile->setTimezone))
            ->where('ended_at', '<', $this->to->addDay()->setTimezone($this->billingProfile->setTimezone));

        return tap($query, function($query) {
            Log::debug('Charges SQL', [
                'sql' => $query->toSql(),
                'bindings' => $query->getBindings()
            ]);
        });
    }
}
