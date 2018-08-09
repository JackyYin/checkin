<?php

namespace App\Http\Requests\Api\V2\Bot;

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
            'name'            => 'required',
            'auth_hook_url'   => 'url',
            'notify_hook_url' => 'url',
        ];
    }
}
