<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use mathewparet\LaravelPolicyAbilitiesExport\Traits\ExportsPermissions;

/**
 * @property \App\Models\Team $team
 * @property string $name
 * @property \Carbon\Carbon $activated_on
 * @property \Carbon\Carbon $deactivated_on
 * @property int $bill_day
 * @property string $timezone
 * @property double $latitude
 * @property double $longitude
 * @property int $radius
 * @property string $address
 * @property array[\App\Models\Vehicle] $vehicles
 * @property array[\App\Models\Bill] $bills
 */
class BillingProfile extends Model
{
    use HasFactory;
    use ExportsPermissions;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'activated_on',
        'deactivated_on',
        'bill_day',
        'timezone',
        'latitude',
        'longitude',
        'radius',
        'address',
    ];

    protected $casts = [
        'activated_on' => 'date:Y-m-d',
        'deactivated_on' => 'date:Y-m-d',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function scopeActive($query)
    {
        return $query->where('activated_on', '<=', now())
                    ->where(fn($query) => $query->whereNull('deactivated_on')->orWhere('deactivated_on', '>=', now()));
    }

    public function scopeInactive($query)
    {
        return $query->where('activated_on', '>', now())
                    ->orWhere(fn($query) => $query->whereNotNull('deactivated_on')->where('deactivated_on', '<=', now()));
    }

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }

    public function bills()
    {
        return $this->hasMany(Bill::class);
    }
}
