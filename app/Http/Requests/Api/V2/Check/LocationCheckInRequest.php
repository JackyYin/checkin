<?php

namespace App\Http\Requests\Api\V2\Check;

use App\Http\Requests\FormRequest;
use App\Helpers\GeoHelper;
use App\Helpers\LeaveHelper;
use Carbon\Carbon;

class LocationCheckinRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'latitude'   => 'bail|required',
            'longitude'  => 'bail|required',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // 距離果子云500公尺以上,不符合GPS打卡資格
            if (!GeoHelper::distanceWithin($this->latitude, $this->longitude, '25.045004', '121.525271', '0.5')) {
                $validator->errors()->add('location', '距離公司太遠');
            }

            // 檢查今日是否已打上班卡
            if ($this->user()->canCheckOutToday()) {
                $validator->errors()->add('checked', '您已打過上班卡');
            }

            // 檢查打卡開始時間是否在其他請假區間內
            if($this->user()->illegalCheckinTime(Carbon::now())) {
                $validator->errors()->add('repeat', '已存在重複的請假時間');
            }
        });
    }
}
