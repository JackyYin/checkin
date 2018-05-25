<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use Validator;
use App\Models\Staff;
use App\Models\Manager;

class ManagerController extends Controller
{
    public function assign(Request $request)
    {
        $messages = [
            'staff_id.required' => '請輸入姓名',
            'staff_id.exists'   => '不存在的使用者',
        ];
        $validator = Validator::make($request->all(), [
            'staff_id'   => array(
                'required',
                'exists:staffs,id',
            ),
        ], $messages);

        if ($validator->fails()) {
            $array = $validator->errors()->all();
            return response(implode(",",$array), 400);
        }

        $staff = Staff::find($request->input('staff_id'));

        if ($staff->admin) {
            return response("此員工已是admin", 400);
        }
        if ($staff->manager) {
            return response("此員工已是管理者", 400);
        }

        Manager::create([
            'staff_id'  => $staff->id,
            'name'      => $staff->name,
            'email'     => $staff->email,
            'password'  => bcrypt(123456),
        ]);

        return response("管理者指派成功", 200);
    }
}
