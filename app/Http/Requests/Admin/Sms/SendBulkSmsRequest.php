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
            // Derived from the sender's access tier by the controller; a client value is ignored.
            'recipient_type' => ['nullable', 'string', 'in:normal,advertising'],
            'campaign_name' => ['nullable', 'string', 'max:100'],
            'service_type' => ['nullable', 'string', 'in:advertising,otp'],
            'otp_category' => ['nullable', 'string', \Illuminate\Validation\Rule::in(\App\Enums\SmsOtpCategory::values())],
            'message' => ['nullable', 'string'],
            'sms_message_template_id' => ['nullable', 'integer', 'exists:sms_templates,id'],
            'sms_message_group_id' => ['nullable'],
            'type' => ['nullable', 'string', 'in:regular,classified'],
            'classification' => ['nullable', 'string', 'in:regular,classified'],
            // The OTP panel always uses the single system line, so only the promotional panel picks one.
            'sender_number_id' => ['required_unless:service_type,otp', 'nullable', 'integer', 'exists:ghorfe_settings,id'],
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
            'recipient_type.in' => 'نوع مخاطبین باید عادی یا تبلیغاتی باشد.',
            'sender_number_id.required_unless' => 'انتخاب سرشماره تبلیغاتی الزامی است.',
            'sender_number_id.exists' => 'سرشماره انتخاب شده معتبر نیست.',
            'otp_category.in' => 'دسته‌بندی سیستمی OTP نامعتبر است.',
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

            $isOtp = $this->input('service_type') === 'otp';

            if ($isOtp) {
                if (!app(\App\Services\Sms\SmsAccessTierService::class)->canUseOtp(auth()->user())) {
                    $validator->errors()->add('service_type', 'دسترسی به پنل OTP برای سطح دسترسی شما مجاز نیست.');
                }
                if (empty($this->input('otp_category'))) {
                    $validator->errors()->add('otp_category', 'برای پنل OTP انتخاب دسته‌بندی سیستمی الزامی است.');
                }

                return;
            }

            // Promotional flow only: OTP templates never go through the promotional panel.
            if (!empty($templateId)
                && \App\Models\SmsTemplate::whereKey($templateId)->where('service_type', 'otp')->exists()) {
                $validator->errors()->add('sms_message_template_id', 'قالب‌های کد تأیید (OTP) برای ارسال تبلیغاتی انبوه مجاز نیستند.');
            }
        });
    }
}
