<?php

namespace App\Http\Requests\api\V2\Leave;

use App\Http\Requests\FormRequest;
use App\Helpers\LeaveHelper;

class StoreRequest extends FormRequest
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
            'type'         => 'bail|required|integer|min:1',
            'checkin_at'   => 'bail|required|date_format:Y-m-d H:i:s|before:checkout_at',
            'checkout_at'  => 'bail|required|date_format:Y-m-d H:i:s|after:checkin_at',
            'reason'       => 'bail|required',
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
            if(!LeaveHelper::CheckRepeat($this->user()->id, $this->checkin_at, $this->checkout_at)) {
                $validator->errors()->add('repeat', '已存在重複的請假時間');
            }
        });
    }
}
