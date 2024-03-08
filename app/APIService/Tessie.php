<?php
namespace App\APIService;

use Exception;
use Carbon\Carbon;
use App\Contracts\TeslaAPIService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\APIService\TeslaAPIService as APIServiceTeslaAPIService;

class Tessie extends APIServiceTeslaAPIService implements TeslaAPIService
{
    private $token;

    public function validateCredentials($config)
    {
        try{
            $this->useAccount($config)->getVehicles();
        }
        catch(Exception $e)
        {
            return false;
        }
        
        return true;
    }

    public function getCharges(string $vin, Carbon $from, Carbon $to): Collection
    {
        $params = [
            'url' => $this->config['url'],
            'vin'=> $vin,
            'from' => $from->timestamp,
            'to' => $to->timestamp,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'radius' => $this->radius,
            'timezone' => $this->timezone,
        ];

        $response = HTTP::withToken($this->token)->get(__(':url/:vin/charges?from=:from&to=:to&origin_latitude=:latitude&origin_longitude=:longitude&origin_radius=:radius&timezone=:timezone', $params));

        $charges = collect($response->json()['results']);

        return $charges->map(fn($charge) => [
            'started_at' => $charge['started_at'],
            'ended_at' => $charge['ended_at'],
            'latitude' => $charge['latitude'],
            'longitude' => $charge['longitude'],
            'cost' => $charge['cost'],
            'starting_battery' => $charge['starting_battery'],
            'ending_battery' => $charge['ending_battery'],
            'energy_used' => $charge['energy_used'],
        ]);
    }

    public function getVehicles(): Collection 
    {
        return Cache::remember('tesla-vehicles-'.request()->user()->currentTeam->id.'-'.sha1($this->token), 60, function() {
            $response = HTTP::withToken($this->token)->get(__(':url/vehicles', [
                'url' => $this->config['url'],
            ]));
    
            $results = collect($response->json()['results']);

            return $results->map(fn($vehicle) => [
                    'plate' => $vehicle['plate'],
                    'vin'  => $vehicle['vin'],
                    'name' => $vehicle['last_state']['display_name']
            ]);
        });
    }

    public function useAccount($config): TeslaAPIService
    {
        $this->token = $config['token'];

        return $this;
    }
}