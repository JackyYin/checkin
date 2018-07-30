<?php

namespace App\Http\Requests\Api\V2\Leave\Stat;

use Illuminate\Foundation\Http\FormRequest;

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
}
