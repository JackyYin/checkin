<?php

namespace App\Http\Requests\Api\V2\Auth;

use App\Http\Requests\FormRequest;

class LoginSocialRequest extends FormRequest
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
            'social_access_token' => 'required',
            'provider'            => 'in: facebook,line'
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
