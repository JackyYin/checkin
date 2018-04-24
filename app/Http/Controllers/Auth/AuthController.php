<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Passport\ClientRepository;
use Route;

class AuthController extends Controller
{
    protected $guard = 'api';

    public function login(Request $request, ClientRepository $client)
    {
        $client = $client->find(2);

        $request->request->add([
            'grant_type' => 'client_credentials',
            'client_id' => $client->id,
            'client_secret' => $client->secret,
        ]);

        $proxy = Request::create(
            'oauth/token',
            'POST'
        );

        $response = json_decode(Route::dispatch($proxy)->getContent());

        if (isset($response->error)) {
            return response()->error('invalid credentials', 400);
        }

        $request->headers->set('Authorization', 'Bearer ' . $response->access_token);

        return response()->json($response);
    }
}
