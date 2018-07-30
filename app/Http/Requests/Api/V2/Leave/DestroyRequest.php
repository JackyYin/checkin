<?php

namespace App\Http\Requests\Api\V2\Leave;

use App\Http\Requests\FormRequest;

class DestroyRequest extends FormRequest
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
            'leaveId' => 'integer|exists:checks,id',
        ];
    }

    /**
     * Add route parameters to validation
     */
    protected function getValidatorInstance()
    {
        $this->merge($this->route()->parameters);

        /*modify data before send to validator*/

        return parent::getValidatorInstance();
    }
}
