<?php

namespace App\Http\Requests\Admin\Sms;

use Illuminate\Foundation\Http\FormRequest;

class SendBulkSmsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'recipient_type' => ['required', 'string', 'in:normal,advertising'],
            'message' => ['nullable', 'string'],
            'sms_message_template_id' => ['nullable', 'integer', 'exists:sms_message_templates,id'],
            'sms_message_group_id' => ['nullable', 'integer', 'exists:sms_message_groups,id'],
            'type' => ['nullable', 'string', 'in:regular,classified'],
            'classification' => ['nullable', 'string', 'in:regular,classified'],
            'sender_number_id' => ['required', 'integer', 'exists:sender_numbers,id'],
            'filters' => ['nullable', 'array'],
            'scheduled_at' => ['nullable', 'date'],
            'custom_numbers' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'recipient_type.required' => 'انتخاب نوع مخاطبین الزامی است.',
            'recipient_type.in' => 'نوع مخاطبین باید عادی یا تبلیغاتی باشد.',
            'sender_number_id.required' => 'انتخاب شماره فرستنده تبلیغاتی الزامی است.',
            'sender_number_id.exists' => 'شماره فرستنده انتخاب شده معتبر نیست.',
            'sms_message_template_id.exists' => 'قالب پیامک انتخاب شده معتبر نیست.',
            'scheduled_at.date' => 'تاریخ و زمان ارسال زمان‌بندی شده نامعتبر است.',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $msg = trim((string) $this->input('message', ''));
            $templateId = $this->input('sms_message_template_id');

            if ($msg === '' && empty($templateId)) {
                $validator->errors()->add('message', 'متن پیامک یا انتخاب قالب الزامی است.');
            }
        });
    }
}
