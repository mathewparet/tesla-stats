<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use mathewparet\LaravelPolicyAbilitiesExport\Traits\ExportsPermissions;

/**
 * @method withinLocation($latitude, $longitude, $radius)
 * @method from(\Carbon\Carbon $from)
 * @method to(\Carbon\Carbon $to)
 * @method \Illuminate\Database\Eloquent\Relations\BelongsTo vehicle()
 */
class Charge extends Model
{
    use HasFactory;
    use ExportsPermissions;

    protected $fillable = [
        'started_at',
        'ended_at',
        'latitude',
        'longitude',
        'cost',
        'starting_battery',
        'ending_battery',
        'energy_consumed',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function scopeFrom($query, Carbon $from)
    {
        return $query->where('started_at', '>=', $from);
    }
    
    public function scopeTo($query, Carbon $to)
    {
        return $query->where('started_at', '>=', $to);
    }

    public function scopeWithinLocation($query, $latitude, $longitude, $radius)
    {
        return $query->select('*')
                ->selectRaw($this->haversineFormula($latitude, $longitude))
                ->having('distance', '<', $radius);

    }

    private function haversineFormula($latitude, $longitude)
    {
        return __(
            '(6371000 * acos(cos(radians(:latitude)) * cos(radians(latitude)) * cos(radians(longitude) - radians(:longitude)) + sin(radians(:latitude)) * sin(radians(latitude)))) AS distance', 
            compact('latitude', 'longitude')
        );
    }
}
