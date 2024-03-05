<?php
namespace App\APIService;

use Illuminate\Support\Arr;
use App\Contracts\TeslaAPIServiceManager as ContractsTeslaAPIServiceManager;

class TeslaAPIServiceManager implements ContractsTeslaAPIServiceManager
{
    private static $providers = [];

    public function provider($name)
    {
        if(!isset(self::$providers[$name]))
        {
            $providerClass = $this->getProviderClass($name);

            self::$providers[$name] = new $providerClass($this->getProviderConfig($name));
        }

        return self::$providers[$name];
    }

    private function readConfigFileParam($name, $config)
    {
        return config('tesla.providers.'.$name.'.'.$config);
    }

    private function getProviderConfig($name)
    {
        return $this->readConfigFileParam($name, "config");
    }

    private function getProviderClass($name)
    {
        return $this->readConfigFileParam($name, "class");
    }

    public function getProviders()
    {
        $providers = collect();

        foreach(config('tesla.providers') as $name => $config)
        {
            $providers->push($name);
        }

        return $providers->all();
    }
}