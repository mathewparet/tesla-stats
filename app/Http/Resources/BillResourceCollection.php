<?php

namespace App\Http\Resources;

use App\Models\Bill;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use mathewparet\LaravelPolicyAbilitiesExport\Resources\ResourceCollectionWithPermissions;

class BillResourceCollection extends ResourceCollectionWithPermissions
{
    /**
     * @var \Illuminate\Database\Eloquent\Model|string $model 
     */
    protected $model = Bill::class;
}