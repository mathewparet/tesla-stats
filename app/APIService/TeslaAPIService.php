<?php
namespace App\APIService;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;

class TeslaAPIService
{
    protected $http;

    public function __construct(protected $config = [])
    {
       
    }
}