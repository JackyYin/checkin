<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use Validator;
use Carbon\Carbon;


class CheckController extends Controller
{
    public function __construct()
    {
    
    }

    public function index()
    {
        $user = Auth::guard('web')->user();
        
        $checked = $user->checked_today();
   
        return view('web.pages.check.index', compact('checked'));
    }

}
