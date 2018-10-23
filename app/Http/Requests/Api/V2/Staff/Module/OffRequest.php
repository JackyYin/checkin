<?php

namespace App\Http\Requests\Api\V2\Staff\Module;

use App\Http\Requests\FormRequest;

class OffRequest extends FormRequest
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
            'module_name'   => 'bail|required|in:fortune|exists:modules,name',
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
            if ($this->user()->modules()->where('name', $this->module_name)->get()->isEmpty()) {
                $validator->errors()->add('repeat', '此模組未啟用');
            }
        });
    }
}
