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
            // «کاهش تعداد مخاطبین»: capped again server-side by what the balance can afford.
            'max_recipients' => ['nullable', 'integer', 'min:1'],
            // Outgoing-queue entry this send was resumed from; removed once the send succeeds.
            'queue_id' => ['nullable', 'integer'],
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
            'max_recipients.integer' => 'تعداد مخاطبین باید عدد صحیح باشد.',
            'max_recipients.min' => 'تعداد مخاطبین باید حداقل ۱ باشد.',
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

            // Promotional sends only support event/greeting, sales and support subject categories.
            if (!empty($this->input('sms_message_group_id')) && !$validator->errors()->has('sms_message_group_id')
                && !\App\Models\SmsMessageGroup::isPromotionalId($this->input('sms_message_group_id'))) {
                $validator->errors()->add('sms_message_group_id', 'دسته‌بندی موضوعی انتخاب‌شده برای پیامک تبلیغاتی مجاز نیست (فقط مناسبتی/تبریک، فروش و پشتیبانی).');
            }

            // Promotional flow only: OTP templates never go through bulk.
            if (!empty($templateId)
                && \App\Models\SmsMessageTemplate::whereKey($templateId)->where('service_type', 'otp')->exists()) {
                $validator->errors()->add('sms_message_template_id', 'قالب‌های کد تأیید (OTP) برای ارسال تبلیغاتی انبوه مجاز نیستند.');
            }
        });
    }
}
