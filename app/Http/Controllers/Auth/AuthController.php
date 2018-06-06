<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Passport\ClientRepository;
use Route;

class AuthController extends Controller
{
    protected $guard = 'api';

    public function login()
    {
		return response('invalid credentials', 400);
    }
}
