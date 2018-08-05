<?php

namespace App\Http\Requests\Api\V2\Leave\Record;

use App\Http\Requests\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Exceptions\HttpResponseException;

class IndexRequest extends FormRequest
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
            'start_date' => 'date_format:Y-m-d',
            'end_date'   => 'date_format:Y-m-d',
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
            if ($this->filled('start_date') && $this->filled('end_date')
            && strtotime($this->end_date." 00:00:00") <= strtotime($this->start_date." 00:00:00")) {
                $validator->errors()->add('start_date', '起始時間需在結束時間之前');
            }

            if (!$this->user()->admin && !$this->user()->manager) {
                if ($this->header('Accept') == 'text/plain') {
                    throw new HttpResponseException(
                        response("權限不足", JsonResponse::HTTP_FORBIDDEN)
                        ->header('Content-Type', 'text/plain')
                    );
                }

                throw new HttpResponseException(response()->json([
                    'reply_message' => [
                        'permission' => [
                            "權限不足"
                        ]
                    ]
                ], JsonResponse::HTTP_FORBIDDEN));
            }
        });
    }
}
