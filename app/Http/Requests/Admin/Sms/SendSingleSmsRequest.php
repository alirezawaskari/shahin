<?php

namespace App\Http\Requests\Admin\Sms;

use Illuminate\Foundation\Http\FormRequest;

class SendSingleSmsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'recipient_phone' => ['required', 'string', 'regex:/^(09|\+989|9)[0-9]{9}$/'],
            'message' => ['required', 'string', 'min:1'],
            'service_type' => ['required', 'string', 'in:otp,advertising,normal'],
            'sender_number_id' => ['nullable', 'integer', 'exists:sender_numbers,id'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'template_id' => ['nullable', 'integer', 'exists:sms_message_templates,id'],
            'auto_append_cancel' => ['nullable', 'boolean'],
            'context' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'recipient_phone.required' => 'وارد کردن شماره گیرنده الزامی است.',
            'recipient_phone.regex' => 'شماره همراه گیرنده نامعتبر است (مثال: 09123456789).',
            'message.required' => 'متن پیامک نمی‌تواند خالی باشد.',
            'service_type.required' => 'نوع سرویس پیامک الزامی است.',
            'service_type.in' => 'نوع سرویس انتخاب شده معتبر نیست.',
            'sender_number_id.exists' => 'شماره فرستنده انتخاب شده معتبر نیست.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('recipient_phone')) {
            $phone = (string) $this->recipient_phone;
            // Normalize Persian / Arabic digits to English
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

            $this->merge(['recipient_phone' => $phone]);
        }
    }
}
