<?php

namespace App\Http\Resources;

use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use mathewparet\LaravelPolicyAbilitiesExport\Resources\ResourceCollectionWithPermissions;

class VehicleResourceCollection extends ResourceCollectionWithPermissions
{
    /**
     * @var \Illuminate\Database\Eloquent\Model|string $model 
     */
    protected $model = Vehicle::class;
}