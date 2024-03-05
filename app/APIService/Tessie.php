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
        $response = HTTP::withToken($this->token)->get(__(':url/:vin/charges?from=:from&to=:to', [
            'url' => $this->config['url'],
            'vin'=> $vin,
            'from' => $from->timestamp,
            'to' => $to->timestamp,
        ]));

        return collect($response->json()['results']);
    }

    public function getVehicles(): Collection 
    {
        return Cache::remember('tesla-vehicles-'.request()->user()->currentTeam->id.'-'.sha1($this->token), 60, function() {
            $response = HTTP::withToken($this->token)->get(__(':url/vehicles', [
                'url' => $this->config['url'],
            ]));
    
            return collect($response->json()['results']);
        });
    }

    public function useAccount($config): TeslaAPIService
    {
        $this->token = $config['token'];

        return $this;
    }
}