<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWorkSampleRequest extends FormRequest
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
            // ajax upload: the widget submits the temp media id in image_path, not a file.
            // media.id is a char(36) UUID, NOT an integer.
            'image_path' => ['nullable', 'string', 'max:36', 'exists:media,id'],
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
            'image_path.exists' => 'تصویر انتخاب شده معتبر نیست؛ لطفا دوباره بارگذاری کنید.',
            'image_path.max' => 'تصویر انتخاب شده معتبر نیست؛ لطفا دوباره بارگذاری کنید.',
            'center_id.required' => 'لطفاً شعبه را انتخاب کنید.',
            'center_id.exists' => 'شعبه انتخاب شده معتبر نیست.',
            'worksampleable_type.required' => 'لطفاً مدل مربوطه را انتخاب کنید.',
            'worksampleable_id.required' => 'لطفاً شناسه مدل مربوطه را وارد کنید.',
            'worksampleable_id.integer' => 'شناسه مدل باید عدد باشد.',
        ];
    }
}
