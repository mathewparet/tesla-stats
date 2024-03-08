<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use mathewparet\LaravelPolicyAbilitiesExport\Traits\ExportsPermissions;

/**
 * @property string $config
 */
class TeslaAccount extends Model
{
    use HasFactory;
    use ExportsPermissions;

    protected $fillable = [
        'provider',
        'config',
    ];

    protected $hidden = [
        'config'
    ];

    protected $casts = [
        'config' => 'encrypted:array',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}
