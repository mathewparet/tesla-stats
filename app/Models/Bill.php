<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
}
