<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use Validator;
use Carbon\Carbon;
use App\Models\Check;

class CheckController extends Controller
{
    public function __construct()
    {
        date_default_timezone_set('Asia/Taipei'); 
    }

    public function index()
    {
        $user = Auth::guard('web')->user();
        
        $checked = $user->checked_today();
   
        return view('web.pages.check.index', compact('checked'));
    }

    public function on()
    {
        $user = Auth::guard('web')->user();

        if (!$user->checked_today()){
            Check::create([
                'user_id'     => $user->id,
                'checkin_at'  => Carbon::now(),
            ]);

            return redirect()->route('web.check.index')->with('success', '打卡上班成功！');
        }

        return redirect()->route('web.check.index')->with('danger', '今日已打過上班卡');
    }

    public function off()
    {
        $user = Auth::guard('web')->user();

        if ($user->checked_today()){
            $check = $user->get_check_today();
            $check->update([
                'checkout_at'   =>  Carbon::now(),
                'hours'         =>  Carbon::now()->diffInHours(Carbon::parse($check->checkin_at)),
                'status'        =>  0
            ]);
            return redirect()->route('web.check.index')->with('success', '打卡下班成功！');
        }

        return redirect()->route('web.check.index')->with('danger', '還沒上班就想下班？');
    }
}
