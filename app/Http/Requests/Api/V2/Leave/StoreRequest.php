<?php

namespace App\Http\Requests\api\V2\Leave;

use App\Http\Requests\FormRequest;

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
            'leave_type'   => 'required|integer|min:1',
            'start_time'   => 'required|date_format:Y-m-d H:i|before:end_time',
            'end_time'     => 'required|date_format:Y-m-d H:i|after:start_time',
            'leave_reason' => 'required',
        ];
    }
}
