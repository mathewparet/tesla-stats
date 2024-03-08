<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use mathewparet\LaravelPolicyAbilitiesExport\Traits\ExportsPermissions;

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
}
