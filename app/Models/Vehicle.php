<?php

namespace App\Models;

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

    protected $fillable = [
        'name',
        'plate',
        'vin',
        'billing_profile_id',
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

    public static function booted()
    {
        static::creating(function ($vehicle) {
            $vehicle->key = sha1($vehicle->vin);
        });
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function billingProfile()
    {
        return $this->belongsTo(BillingProfile::class);
    }
}
