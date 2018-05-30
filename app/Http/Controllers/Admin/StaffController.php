<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use Validator;
use App\Models\Staff;
use App\Models\Profile;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $staffs = Staff::with(['admin', 'manager'])
        ->whereHas('profile', function ($query) {
            $query->where('identity', '!=', 2);
        })
        ->where(function ($query) use ($request) {
            $keyword = $request->input('keyword');

            if (!empty($keyword)) {
                $search = "%{$keyword}%";

                $query->where("name", "LIKE", $search)
                    ->orWhere("email", "LIKE", $search);
            }

        })
        ->get()
        ->sort(function ($a, $b) {
            if (!$a->staff_code) {
                return $b->staff_code ? 1  : 0;
            }
            if (!$b->staff_code) {
                return -1;
            }
            if ($a->staff_code == $b->staff_code) {
                return 0;
            }

            return $a->staff_code < $b->staff_code ? -1 : 1;
        });

        return view('admin.pages.staff.index', compact('staffs'));
    }

    public function create()
    {
        $options = $this->getFormOptions();

        return view('admin.pages.staff.form', compact('options')); 
    }

    public function store(Request $request)
    {
        $messages = [
            'name.required'                     => "請輸入姓名",
            'email.required'                    => "請輸入email",
            'email.email'                       => "請輸入有效的email",
            'email.unique'                      => "已註冊的email",
            'ID_card_number.required'           => "請輸入身分證字號或居留證號碼",
            'ID_card_number.regex'              => "請輸入有效的身分證字號或居留證號碼",
            'ID_Card_number.unique'             => "已註冊的身分證字號或居留證號碼",
            'staff_code.unique'                 => "已使用的員工編號",
            'on_board_date.date_format'         => "請輸入格式：Y-m-d",
            'birth.date_format'                 => "請輸入格式：Y-m-d",
            'add_insurance_date.date_format'    => "請輸入格式：Y-m-d",
            'cancel_insurance_date.date_format' => "請輸入格式：Y-m-d",
        ];
        $ID_regex = "(^[a-zA-Z]{1}[abcdABCD]{1}[0-9]{8}$|^[a-zA-Z]{1}[1-2]{1}[0-9]{8}$)";
        $validator = Validator::make($request->all(), [
            'name'                  => 'required',
            'email'                 => 'required|email|unique:staffs,email|unique:staff_profile,email',
            'ID_card_number'        => [
                'required',
                'regex:'.$ID_regex,
                'unique:staff_profile,ID_card_number',
            ], 
            'staff_code'            => 'nullable|unique:staffs,staff_code|unique:staff_profile,staff_code',
            'on_board_date'         => 'date_format:Y-m-d',
            'birth'                 => 'date_format:Y-m-d',
            'add_insurance_date'    => 'date_format:Y-m-d',
            'cancel_insurance_date' => 'nullable|date_format:Y-m-d',
        ], $messages);

        if ($validator->fails()) {
            return redirect()->route('admin.staff.create')->withErrors($validator->errors()); 
        }

        $new_staff = Staff::create([
            'name'       => $request->input('name'),
            'email'      => $request->input('email'),
            'staff_code' => $request->input('staff_code'),
            'active'     => 0,
        ]);

        $profile = Profile::create(array_merge($request->except('cancel_insurance_date'),['staff_id' => $new_staff->id]));

        return redirect()->route('admin.staff.create')->with('success', '員工創建成功!'); 
    }

    public function edit($staff_id)
    {
        $staff = Staff::find($staff_id);
        $options = $this->getFormOptions();

        return view('admin.pages.staff.form', compact('staff', 'options'));
    }

    public function update($staff_id, Request $request)
    {
        $staff = Staff::find($staff_id);

        $messages = [
            'name.required'                     => "請輸入姓名",
            'email.required'                    => "請輸入email",
            'email.email'                       => "請輸入有效的email",
            'email.unique'                      => "已註冊的email",
            'ID_card_number.required'           => "請輸入身分證字號或居留證號碼",
            'ID_card_number.regex'              => "請輸入有效的身分證字號或居留證號碼",
            'ID_card_number.unique'             => "已註冊的身分證字號或居留證號碼",
            'staff_code.unique'                 => "已使用的員工編號",
            'on_board_date.date_format'         => "請輸入格式：Y-m-d",
            'birth.date_format'                 => "請輸入格式：Y-m-d",
            'add_insurance_date.date_format'    => "請輸入格式：Y-m-d",
            'cancel_insurance_date.date_format' => "請輸入格式：Y-m-d",
        ];
        $ID_regex = "(^[a-zA-Z]{1}[abcdABCD]{1}[0-9]{8}$|^[a-zA-Z]{1}[1-2]{1}[0-9]{8}$)";
        $validator = Validator::make($request->all(), [
            'name'                  => 'required',
            'email'                 => [
                'required',
                'email',
                'unique:staffs,email,'.$staff->id,
                'unique:staff_profile,email,'.$staff->profile->id,
            ],
            'ID_card_number'        => [
                'required',
                'regex:'.$ID_regex,
                'unique:staff_profile,ID_card_number,'.$staff->profile->id,
            ], 
            'staff_code'            => [
                'nullable',
                'unique:staffs,staff_code,'.$staff->id,
                'unique:staff_profile,staff_code,'.$staff->profile->id,
            ],
            'on_board_date'         => 'date_format:Y-m-d',
            'birth'                 => 'date_format:Y-m-d',
            'add_insurance_date'    => 'date_format:Y-m-d',
            'cancel_insurance_date' => 'nullable|date_format:Y-m-d',
        ], $messages);

        if ($validator->fails()) {
            return redirect()->route('admin.staff.edit', $staff_id)->withErrors($validator->errors()); 
        }

        $staff->update($request->only(['name', 'email', 'staff_code']));
        $staff->profile->update($request->all());
        if ($staff->admin) {
            $staff->admin->update($request->only(['name', 'email']));
        }
        if ($staff->manager) {
            $staff->manager->update($request->only(['name', 'email']));
        }

        return redirect()->route('admin.staff.index')->with('success', '員工編輯成功！');
    }

    public function assignSubscription (Request $request)
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

        if ($staff->subscribed) {
            return response("此員工已訂閱", 400);
        }

        $staff->subscribed = 1;
        $staff->save();

        return response("訂閱成功", 200);
    }

    private function getFormOptions ()
    {
        $options['identity'] = array(
            Profile::ID_FULL_TIME => '全職',
            Profile::ID_PART_TIME => '工讀',
            Profile::ID_RESIGNED  => '離職',
        );

        return $options;
    }
}
