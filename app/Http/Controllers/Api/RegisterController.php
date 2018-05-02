<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use \Gnello\Mattermost\Laravel\Facades\Mattermost;
use Validator;
use Mail;
use App\Models\Staff;
use App\Models\Line;
use App\Models\AuthCode;

class RegisterController extends Controller
{
    /**
     *
     * @SWG\Get(path="/api/register",
     *   tags={"project"},
     *   summary="註冊手續",
     *   operationId="register",
     *   produces={"text/plain"},
     *   @SWG\Parameter(
     *     in="query",
     *     name="email",
     *     type="string",
     *     required=true,
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="line_id",
     *     type="string",
     *     required=true,
     *   ),
     *   @SWG\Response(response="default", description="操作成功")
     * )
     */

    public function register(Request $request)
    {
        $messages = [
            'email.required'   => '請填入email',
            'email'            => '請填入有效的email',
            'email.exists'     => '不存在的email,請先登錄員工個人資料',
            'line_id.required' => '請填入line_id',
        ];
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email|exists:staffs,email',
            'line_id'  => 'required'
        ], $messages);

        if ($validator->fails()) {
            $array = $validator->errors()->all();
            return implode(",",$array);
        }

        $new_staff = Staff::where('email', $request->input('email'))->first();

        if (!$new_staff) {
            return "不存在的email,請先登錄員工個人資料";
        }

        Line::create([
            'staff_id' => $new_staff->id,
            'line_id'  => $request->input('line_id'),
        ]);
        //驗證碼
        $auth_code = "AU".str_random(4);
        AuthCode::create([
            'staff_id'  => $new_staff->id,
            'auth_code' => $auth_code
        ]);

        $this->sendAuthCodeEmail($new_staff, $auth_code);

        return "請至信箱確認驗證碼.";
    }

    private function sendAuthCodeEmail(Staff $staff, $auth_code)
    {
        Mail::send('emails.authcode',['staff' => $staff, 'code' => $auth_code], function ($message) use($staff, $auth_code) {
            $message->from(env('MAIL_FROM_ADDRESS'),env('MAIL_FROM_NAME'))
            ->to($staff->email, $staff->name)
            ->subject("您好,您的驗證碼為".$auth_code);
        } );
    }

    /**
     *
     * @SWG\Get(path="/api/active",
     *   tags={"project"},
     *   summary="註冊驗證手續",
     *   operationId="active",
     *   produces={"text/plain"},
     *   @SWG\Parameter(
     *     in="query",
     *     name="auth_code",
     *     type="string",
     *     required=true,
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="line_id",
     *     type="string",
     *     required=true,
     *   ),
     *   @SWG\Response(response="default", description="操作成功")
     * )
     */
    public function active(Request $request)
    {
        $messages = [
            'auth_code.required' => '請填入驗證碼',
            'auth_code.exists'   => '不符合的驗證碼',
            'line_id.required'   => '請填入line_id',
            'line_id.exists'     => '不存在的line_id'
        ];
        $validator = Validator::make($request->all(), [
            'auth_code' => 'required|exists:staff_auth,auth_code',
            'line_id'   => 'required|exists:staff_line,line_id'
        ], $messages);

        if ($validator->fails()) {
            $array = $validator->errors()->all();
            return implode(",",$array);
        }

        $line = Line::where('line_id', $request->input('line_id'))->first();

        if (!$line) {
            return "不存在的line_id";
        }

        $staff = $line->staff;

        if ($staff->authcode->matchCode($request->input('auth_code'))) {
            $staff->authcode->delete();
            $staff->active = Staff::ACTIVE;
            $staff->save();

            return "帳號已啟用";
        }

        return "驗證碼不符合";
    }

    private function getMMName($email)
    {
        $driver = Mattermost::server('default');

        $result = $driver->getUserModel()->getUserByEmail($email);

        if ($result->getStatusCode() == 200) {
            return json_decode($result->getBody())->nickname;
        } else {
            return false;
        }
    }
}
