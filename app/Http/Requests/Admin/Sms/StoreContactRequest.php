<?php

namespace App\Http\Requests\Admin\Sms;

use Illuminate\Foundation\Http\FormRequest;

class StoreContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'phone' => ['required', 'string', 'regex:/^(09|\+989|9)[0-9]{9}$/'],
            'name' => ['required', 'string', 'max:190'],
            'family' => ['nullable', 'string', 'max:190'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.required' => 'شماره تلفن الزامی است.',
            'phone.regex' => 'شماره همراه نامعتبر است (مثال: 09123456789).',
            'name.required' => 'نام مخاطب الزامی است.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('phone')) {
            $phone = (string) $this->phone;
            $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
            $arabic = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
            $english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
            $phone = str_replace($persian, $english, $phone);
            $phone = str_replace($arabic, $english, $phone);
            $phone = preg_replace('/[^\d+]/', '', $phone);

            if (str_starts_with($phone, '+98')) {
                $phone = '0' . substr($phone, 3);
            } elseif (str_starts_with($phone, '98') && strlen($phone) === 12) {
                $phone = '0' . substr($phone, 2);
            } elseif (str_starts_with($phone, '9') && strlen($phone) === 10) {
                $phone = '0' . $phone;
            }

            $this->merge(['phone' => $phone]);
        }
    }
}
