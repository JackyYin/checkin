<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;
use Validator;
use Mail;
use App\Models\Staff;
use App\Models\Line;
use App\Models\RegistrationToken;

class RegisterController extends Controller
{
	/**
	 * @SWG\Tag(name="Auth", description="使用者認證")
	 */
    /**
     *
     * @SWG\Post(path="/api/v2/register",
     *   tags={"Auth", "V2"},
     *   security={
     *   	{"bot": {}},
     *   },
     *   summary="註冊手續",
     *   operationId="register",
     *   produces={"text/plain"},
     *   @SWG\Parameter(
     *       name="email",
     *       in="formData",
     *       type="string",
     *       required=true,
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
        ];
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email|exists:staffs,email',
        ], $messages);

        if ($validator->fails()) {
            $array = $validator->errors()->all();
            return implode(",",$array);
        }

        $new_staff = Staff::where('email', $request->input('email'))->first();

        if (!$new_staff) {
            return "不存在的email,請先登錄員工個人資料";
        }

        //驗證url
        $registration_token = Uuid::uuid4();
        RegistrationToken::create([
            'staff_id' => $new_staff->id,
            'token'    => sha1($registration_token),
        ]);
        $confirmation_url = route('api.register.active', ['registration_token' => $registration_token]);
        $this->sendRegistrationEmail($new_staff, $confirmation_url);

        return "請至信箱確認驗證信件.";
    }

    private function sendRegistrationEmail(Staff $staff, $confirmation_url)
    {
        Mail::send('emails.registration',['staff' => $staff, 'url' => $confirmation_url], function ($message) use($staff) {
            $message->from(env('MAIL_FROM_ADDRESS'),env('MAIL_FROM_NAME'))
            ->to($staff->email, $staff->name)
            ->subject("您好,請點擊連結以啟用帳號");
        } );
    }

    /**
     *
     * @SWG\Get(path="/api/v2/register/active/{registration_token}",
     *   tags={"Auth", "V2"},
     *   summary="註冊驗證手續",
     *   operationId="active",
     *   produces={"text/plain"},
     *   @SWG\Parameter(
     *       name="registration_token",
     *       in="path",
     *       type="string",
     *       required=false,
     *   ),
     *   @SWG\Response(response="default", description="操作成功")
     * )
     */
    public function active($registration_token)
    {
        if (!$registration_token) {
            abort(404, 'Girlfriend not found.');
        }

        $token_object = RegistrationToken::where('token', sha1($registration_token))->first();

        if (!$token_object) {
            return "帳號驗證失敗";
        }

        $staff = $token_object->staff;

        if ($staff->active == Staff::ACTIVE) {
            $staff->registration_token()->delete();
            return "已啟用的帳號";
        }

        $staff->update([
            'active' => Staff::ACTIVE,
        ]);
        $staff->registration_token()->delete();
        //送token給line-bot
        $access_token = $staff->createToken('Api User')->accessToken;

        return "帳號驗證成功";
    }
}
