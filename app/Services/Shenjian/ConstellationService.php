<?php

namespace App\Services\Shenjian;

use DB;
use GuzzleHttp\Client;
use App\Models\Staff;
use App\Models\Bot;

class ConstellationService
{
    protected $base_url;
    protected $app_id;
    protected $client;

    public function __construct()
    {
        $this->base_url = "https://api.shenjian.io/constellation";
        $this->app_id = config('shenjian.appid');
        $this->client = new Client;
    }

    public function today($constellation)
    {
        try {
            $response = $this->client->get($this->base_url.'/today', [
                'query' => [
                    'appid'         => $this->app_id,
                    'constellation' => $constellation
                ],
            ]);
        } catch (\Exception $e) {
            return false;
        }

        $result = json_decode((string) $response->getBody());

        if ($result->error_code != 0) {
            return false;
        }

        return $result;
    }
}
