<?php

namespace App\Http\Requests\Api\V2\Leave;

use App\Http\Requests\FormRequest;
use App\Helpers\LeaveHelper;

class RequestLateRequest extends FormRequest
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
            'start_time'   => 'bail|required|date_format:Y-m-d H:i:s|before:end_time',
            'end_time'     => 'bail|required|date_format:Y-m-d H:i:s|after:start_time',
            'leave_reason' => 'bail|required',
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
            if(!LeaveHelper::CheckRepeat($this->user()->id, $this->start_time, $this->end_time)) {
                $validator->errors()->add('repeat', '已存在重複的請假時間');
            }
        });
    }
}
