<?php
namespace App\APIService;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;

class TeslaAPIService
{
    protected $http, $latitude, $longitude, $radius, $timezone;

    public function __construct(protected $config = [])
    {
       
    }

    /**
     * Set the location for the query
     * 
     * @param double $latitude
     * @param double $longitude
     * @return \App\Contracts\TeslaAPIService
     */
    public function location($latitude, $longitude, $radius)
    {
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->radius = $radius;

        return $this;
    }
    
    /**
     * Set the timezone for the query
     * 
     * @param timezone
     * @return \App\Contracts\TeslaAPIService
     */
    public function timezone($timezone)
    {
        $this->timezone = $timezone;

        return $this;
    }
}