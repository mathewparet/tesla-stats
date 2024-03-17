<?php

namespace App\Http\Resources;

use App\Models\BillingProfile;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use mathewparet\LaravelPolicyAbilitiesExport\Resources\ResourceCollectionWithPermissions;

class BillingProfileResourceCollection extends ResourceCollectionWithPermissions
{
    /**
     * @var \Illuminate\Database\Eloquent\Model|string $model 
     */
    protected $model = BillingProfile::class;
}