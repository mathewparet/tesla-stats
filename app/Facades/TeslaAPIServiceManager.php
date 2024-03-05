<?php
namespace App\Facades;

use App\APIService\TeslaAPIServiceManager as APIServiceTeslaAPIServiceManager;
use App\Contracts\TeslaAPIServiceManager as ContractsTeslaAPIServiceManager;
use Illuminate\Support\Facades\Facade;

class TeslaAPIServiceManager extends Facade
{
    protected static function getFacadeAccessor()
    {
        return APIServiceTeslaAPIServiceManager::class;
    }
}