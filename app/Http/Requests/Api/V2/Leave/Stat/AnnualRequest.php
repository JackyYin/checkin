<?php

namespace App\Http\Requests\Api\V2\Leave\Stat;

use App\Http\Requests\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Exceptions\HttpResponseException;

class AnnualRequest extends FormRequest
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
        return [];
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
            if (!$this->user()->profile || !$this->user()->profile->on_board_date) {
                throw new HttpResponseException(response()->json([
                    'reply_message' => [
                        'on_board_date' => [
                            '查無到職日期'
                        ]
                    ]
                ], JsonResponse::HTTP_NOT_FOUND));
            } 
        });
    }
}
