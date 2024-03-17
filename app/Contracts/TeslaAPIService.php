<?php
namespace App\Contracts;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;

interface TeslaAPIService
{
    /**
     * Get charges between two dates.
     *
     * @param String $vin
     * @param Carbon $from
     * @param Carbon $to
     * @return Collection
     */
    public function getCharges(String $vin, ?Carbon $from, ?Carbon $to): Collection;

    /**
     * Get vehicles.
     *
     * @return Collection
     */
    public function getVehicles(): Collection;

    /**
     * Configure the account to be used
     * 
     * @param array $config
     * @return TeslaAPIService
     */
    public function useAccount($config): TeslaAPIService;

    /**
     * Validate credentials
     * 
     * @param array $config
     * @return boolean
     */
    public function validateCredentials($config);

    /**
     * Set the location of charge
     * 
     * @param $latitude
     * @param $longitude
     * @param radius
     * @return TeslaAPIService
     */
    public function location($latitude, $longitude, $radius);
    
    /**
     * Set the timezone for the charge
     * 
     * @param timezone
     * @return TeslaAPIservice
     */
    public function timezone($timezone);
}