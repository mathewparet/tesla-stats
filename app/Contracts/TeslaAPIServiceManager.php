<?php
namespace App\Contracts;

use App\Contracts\TeslaAPIService;

interface TeslaAPIServiceManager
{
    /**
     * Get available providers
     * 
     * @return array
     */
    public function getProviders();

    /**
     * Get provider
     * 
     * @param string $name
     * @return TeslaAPIService
     */
    public function provider($name);
}