<?php

namespace App\Models;

use App\Traits\HasHashId;
use Laravel\Jetstream\Events\TeamCreated;
use Laravel\Jetstream\Events\TeamDeleted;
use Laravel\Jetstream\Events\TeamUpdated;
use Laravel\Jetstream\Team as JetstreamTeam;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property \App\Models\TeslaAccount $teslaAccount
 * @property array[\App\Models\Vehicle] $vehicles
 * @property array[\App\Models\BillingProfile] $billingProfiles
 */
class Team extends JetstreamTeam
{
    use HasFactory;
    use HasHashId;

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'personal_team' => 'boolean',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'personal_team',
    ];

    /**
     * The event map for the model.
     *
     * @var array<string, class-string>
     */
    protected $dispatchesEvents = [
        'created' => TeamCreated::class,
        'updated' => TeamUpdated::class,
        'deleted' => TeamDeleted::class,
    ];

    public function teslaAccount()
    {
        return $this->hasOne(TeslaAccount::class);
    }

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }

    public function billingProfiles()
    {
        return $this->hasMany(BillingProfile::class);
    }

    public function updateOrCreateVehicle($attributes)
    {
        return $this->vehicles()->updateOrCreate(['key' => sha1($attributes['vin'].'-'.$this->id)], $attributes);
    }

}
