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
        static::creating(fn($bill) => $bill->storeSummary());

        static::created(fn($bill) => $bill->sendBill());
    }

    public function storeSummary()
    {
        $charges = tap($this->getCharges(), function($charges) {
            Log::debug('Charges SQL', [
                'sql' => $charges->toSql(),
                'bindings' => $charges->getBindings()
            ]);
        });

        $this->total_cost = $charges->sum('cost');
        $this->energy_consumed = $charges->sum('energy_consumed');
    }

    public function sendBill()
    {
        if($this->to->lte(now()))
        {
            $this->billingProfile->team->owner->notify(new NewBillReady($this));
        }
    }

    private function getCharges()
    {
        return Charge::whereIn('vehicle_id', $this->billingProfile->vehicles()->pluck('id'))
            ->where('started_at', '>=', $this->from->setTimezone($this->billingProfile->setTimezone))
            ->where('ended_at', '<', $this->to->addDay()->setTimezone($this->billingProfile->setTimezone));
    }
}
