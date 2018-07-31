<?php

namespace App\Http\Requests\Api\V2\Leave;

use App\Http\Requests\FormRequest;
use App\Helpers\LeaveHelper;
use App\Models\Check;

class UpdateRequest extends FormRequest
{
    /**
     * Add route parameters to validation
     */
    protected function getValidatorInstance()
    {
        $this->merge($this->route()->parameters);

        /*modify data before send to validator*/

        return parent::getValidatorInstance();
    }

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
            'leaveId'           => 'integer|exists:checks,id',
            'leave_type'        => 'integer|min:1',
            'start_time'        => 'required|date_format:Y-m-d H:i|before:end_time',
            'end_time'          => 'required|date_format:Y-m-d H:i|after:start_time',
        ];
    }
}
