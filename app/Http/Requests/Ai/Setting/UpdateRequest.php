<?php

namespace App\Http\Requests\Ai\Setting;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'role' => 'required|string',
            'model' => 'required|string',
        ];
    }

    public function messages():array
    {
        return [
            'role.required' => 'لطفا قانوان را تعریف کنید',
            'model.required' => 'لطفا مدل را انتخاب کنید',
        ];
    }
}
