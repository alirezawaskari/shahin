<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWorkSampleRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'status' => ['required', 'boolean'],
            'image' => ['nullable', 'image', 'max:2048'],
            'center_id' => ['required', 'exists:centers,id'],
            'worksampleable_type' => ['required', 'string'],
            'worksampleable_id' => ['required', 'integer'],
        ];
    }
    public function messages(): array
    {
        return [
            'title.required' => 'لطفاً عنوان نمونه‌کار را وارد کنید.',
            'title.max' => 'عنوان نباید بیشتر از ۲۵۵ کاراکتر باشد.',
            'status.required' => 'لطفاً وضعیت را مشخص کنید.',
            'status.boolean' => 'مقدار وضعیت نامعتبر است.',
            'image.image' => 'فایل انتخاب شده باید یک تصویر باشد.',
            'image.max' => 'اندازه تصویر نباید بیشتر از ۲ مگابایت باشد.',
            'center_id.required' => 'لطفاً مرکز را انتخاب کنید.',
            'center_id.exists' => 'مرکز انتخاب شده معتبر نیست.',
            'worksampleable_type.required' => 'لطفاً مدل مربوطه را انتخاب کنید.',
            'worksampleable_id.required' => 'لطفاً شناسه مدل مربوطه را وارد کنید.',
            'worksampleable_id.integer' => 'شناسه مدل باید عدد باشد.',
        ];
    }
}
