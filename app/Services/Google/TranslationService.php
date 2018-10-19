<?php

namespace App\Services\Google;

use Laravel\Socialite\Contracts\User as ProviderUser;
use DB;
use GuzzleHttp\Client;
use App\Models\Staff;
use App\Models\Bot;

class TranslationService
{
    protected $url;
    protected $key;
    protected $client;

    public function __construct()
    {
        $this->url = "https://translation.googleapis.com/language/translate/v2";
        $this->key = config('google.key');
        $this->client = new Client;
    }

    public function translate($content, $target = 'zh_TW')
    {
        try {
            $response = $this->client->get($this->url, [
                'query' => [
                    'key'    => $this->key,
                    'target' => $target,
                    'q'      => $content
                ],
            ]);
        } catch (\Exception $e) {
            return false;
        }

        $result = json_decode((string) $response->getBody());

        return $result->data->translations[0]->translatedText;
    }
}
