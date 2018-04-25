<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use Validator;

class AuthController extends Controller
{
    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $guard = 'admin';
    protected $redirectTo = '/admin/staff';

    public function login()
    {
        if (Auth::guard($this->guard)->check()) {
            return redirect($this->redirectTo);
        }

        return view('admin.pages.auth.login');
    }
  
    /**
     * Handle an authentication attempt.
     *
     * @return Response
     */
    public function authenticate(Request $request)
    {
        $messages = [
            'email.required'     => '請填入email',
            'password.required'  => '請填入密碼',
            'email'              => '請填入有效的email',
            'password.min'       => '密碼長度至少為:min字元',
        ];
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|min:6',
            'remember' => 'boolean',
        ], $messages);

        if ($validator->fails()) {
            return back()->withErrors($validator->errors()); 
        }

        $email    = $request->input('email');
        $pass     = $request->input('password');
        $remember = $request->input('remember');

        if (Auth::guard($this->guard)->attempt(['email' => $email, 'password' => $pass], $remember)) {
            return redirect($this->redirectTo);
        }
          
        return back()->with('danger', '帳號或密碼錯誤.');
    }

}
