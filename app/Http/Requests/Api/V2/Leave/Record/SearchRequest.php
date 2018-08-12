<?php

namespace App\Http\Requests\Api\V2\Leave\Record;

use App\Http\Requests\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Exceptions\HttpResponseException;

class SearchRequest extends FormRequest
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
            'checkin_at'  => 'date_format:Y-m-d H:i:s',
            'checkout_at' => 'date_format:Y-m-d H:i:s',
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
            if ($this->filled('checkin_at') && $this->filled('checkout_at')
            && strtotime($this->checkout_at) <= strtotime($this->checkin_at)) {
                $validator->errors()->add('checkin_at', '起始時間需在結束時間之前');
            }
        });
    }
}
