<?php

namespace App\Http\Requests;

use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest as LaravelFormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Exceptions\HttpResponseException;

abstract class FormRequest extends LaravelFormRequest
{
     /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    abstract public function rules();
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    abstract public function authorize();
    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();

        if ($this->header('Accept') == 'text/plain') {
            throw new HttpResponseException(
                response(implode($errors->all(), ","), JsonResponse::HTTP_BAD_REQUEST)
                ->header('Content-Type', 'text/plain')
            );
        }

        throw new HttpResponseException(response()->json([
            'reply_message' => $errors->messages()
        ], JsonResponse::HTTP_BAD_REQUEST));
    }
}
