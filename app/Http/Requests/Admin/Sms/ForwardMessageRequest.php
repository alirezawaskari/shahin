<?php

namespace App\Http\Requests\Admin\Sms;

use Illuminate\Foundation\Http\FormRequest;

class ForwardMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'original_message_id' => ['required', 'integer', 'exists:groupsms,id'],
            'recipient_phone' => ['required', 'string', 'regex:/^(09|\+989|9)[0-9]{9}$/'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'referrer_id' => ['nullable', 'integer', 'exists:users,id'],
            'referred_at' => ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'original_message_id.required' => 'شناسه پیامک ارجاعی الزامی است.',
            'original_message_id.exists' => 'پیامک مورد نظر یافت نشد.',
            'recipient_phone.required' => 'شماره همراه گیرنده ارجاع الزامی است.',
            'recipient_phone.regex' => 'شماره همراه گیرنده نامعتبر است.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('recipient_phone')) {
            $phone = (string) $this->recipient_phone;
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
