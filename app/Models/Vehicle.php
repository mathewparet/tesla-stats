<?php

namespace App\Models;

use App\Jobs\ImportChargesForVehicleForDuration;
use App\Traits\HasHashId;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use mathewparet\LaravelPolicyAbilitiesExport\Traits\ExportsPermissions;

class Vehicle extends Model
{
    use HasFactory;
    use ExportsPermissions;
    use SoftDeletes;
    use HasHashId;

    protected function getHashIdConnection()
    {
        return 'vehicles';
    }

    protected $fillable = [
        'name',
        'plate',
        'vin',
        'billing_profile_id',
        'key',
    ];

    protected $casts = [
        'vin' => 'encrypted'
    ];

    protected $hidden = [
        'vin',
        'key',
    ];

    protected $appends = [
        'masked_vin'
    ];

    public function maskedVin(): Attribute
    {
        return Attribute::make(
            get: fn () => Str::mask($this->vin, '*',5),
        );
    }

    public function scopeBillable($query)
    {
        return $query->has('billingProfiles');
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function billingProfiles()
    {
        return $this->belongsToMany(BillingProfile::class);
    }

    public function charges()
    {
        return $this->hasMany(Charge::class);
    }
}
