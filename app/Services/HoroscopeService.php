<?php

namespace App\Services;

use App;
use Cache;
use Carbon\Carbon;
use GuzzleHttp\Client;
use App\Models\Staff;
use App\Models\Bot;

class HoroscopeService
{
    protected $url;
    protected $client;

    protected $mapping = [
        '今日運勢－水瓶座' => 'aquarius',
        '今日運勢－雙魚座' => 'pisces',
        '今日運勢－牡羊座' => 'aries',
        '今日運勢－金牛座' => 'taurus',
        '今日運勢－雙子座' => 'gemini',
        '今日運勢－巨蟹座' => 'cancer',
        '今日運勢－獅子座' => 'leo',
        '今日運勢－處女座' => 'virgo',
        '今日運勢－天秤座' => 'libra',
        '今日運勢－天蠍座' => 'scorpio',
        '今日運勢－射手座' => 'sagittarius',
        '今日運勢－摩羯座' => 'capricorn',
    ];

    public function __construct()
    {
        $this->url = "https://horoscope-crawler.herokuapp.com/api/horoscope";
        $this->client = new Client;
    }

    public function today($en_constellation)
    {
        $data = $this->get();

        return $data[$en_constellation];
    }

    private function get()
    {
        $redis_key = Carbon::today()->toDateString().":horoscope";

        if (Cache::has($redis_key)) {
            return Cache::get($redis_key);
        }

        try {
            $response = $this->client->get($this->url);
        } catch (\Exception $e) {
            return false;
        }

        $objects = json_decode((string) $response->getBody());

        $data = [];

        foreach ($objects as $object) {
            $data[$this->mapping[$object->name]] = $object;
        }

        Cache::put($redis_key, $data, Carbon::tomorrow());

        return $data;
    }
}
