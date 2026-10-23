<?php

namespace App\Http\Requests\Admin\Sms;

use App\Enums\SmsOtpCategory;
use App\Models\SmsTemplate;
use App\Services\Sms\SmsAccessTierService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SendSingleSmsRequest extends FormRequest
{
    public function authorize(): bool
    {
        if (!auth()->check()) {
            return false;
        }

        // OTP send is internal-only (Titanium / superAdmin); lower tiers have no OTP access at all.
        if ($this->input('service_type') === 'otp') {
            return app(SmsAccessTierService::class)->canUseOtp(auth()->user());
        }

        return true;
    }

    public function rules(): array
    {
        return [
            'recipient_phone' => ['required', 'string', 'regex:/^(09|\+989|9)[0-9]{9}$/'],
            'message' => ['required', 'string', 'min:1', function ($attribute, $value, $fail) {
                if (trim((string) $value) === '') {
                    $fail('متن پیامک نمی‌تواند خالی باشد.');
                }
            }],
            'service_type' => ['required', 'string', 'in:otp,advertising,normal'],
            'sender_number_id' => ['nullable', 'integer', 'exists:ghorfe_settings,id'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'template_id' => ['nullable', 'integer', 'exists:sms_templates,id'],
            'sms_message_group_id' => ['nullable', 'integer'],
            'type' => ['nullable', 'string', 'in:regular,classified'],
            'classification' => ['nullable', 'string', 'in:regular,classified'],
            'context' => ['nullable', 'array'],
            // Fixed OTP system category; required (here or via the template) for OTP, ignored otherwise.
            'otp_category' => ['nullable', 'string', Rule::in(SmsOtpCategory::values())],
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
            'otp_category.in' => 'دسته‌بندی OTP انتخاب‌شده معتبر نیست.',
            'sender_number_id.exists' => 'شماره فرستنده انتخاب شده معتبر نیست.',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->input('service_type') === 'otp' && !$validator->errors()->has('otp_category')) {
                $templateCategory = null;
                if (!empty($this->input('template_id')) && !$validator->errors()->has('template_id')) {
                    $templateCategory = \App\Models\SmsTemplate::categoryOf((int) $this->input('template_id'));
                }
                $chosen = $this->input('otp_category') ?: null;
                if (!$chosen && !$templateCategory) {
                    $validator->errors()->add('otp_category', 'برای پیامک OTP انتخاب دسته‌بندی سیستمی (احراز هویت، فروش، مالی یا حقوقی) الزامی است.');
                } elseif ($chosen && $templateCategory && $chosen !== $templateCategory) {
                    $validator->errors()->add('otp_category', 'دسته‌بندی انتخابی با دسته‌بندی الگوی OTP مطابقت ندارد.');
                }
            }

            // Promotional sends only support event/greeting, sales and support subject categories (OTP unaffected).
            if ($this->input('service_type') !== 'otp' && !empty($this->input('sms_message_group_id'))
                && !$validator->errors()->has('sms_message_group_id')
                && !\App\Models\Groupsms::isPromotionalGroup($this->input('sms_message_group_id'))) {
                $validator->errors()->add('sms_message_group_id', 'دسته‌بندی موضوعی انتخاب‌شده برای پیامک تبلیغاتی مجاز نیست (فقط مناسبتی/تبریک، فروش و پشتیبانی).');
            }

            $templateId = $this->input('template_id');
            if (empty($templateId) || $validator->errors()->has('template_id')) {
                return;
            }

            $user = auth()->user();
            if ($user && app(SmsAccessTierService::class)->canUseOtp($user)) {
                return;
            }

            $template = \App\Models\SmsTemplate::find($templateId);
            if (!$template) {
                return;
            }

            // The business link is not stored on the template: visibility follows the owner (user_id), like the picker.
            $business = ghorfe();
            $foreign = $business && !$template->isVisibleTo($business);

            if ($template->service_type === SmsTemplate::TYPE_OTP || $foreign) {
                $validator->errors()->add('template_id', 'قالب پیامک انتخاب شده معتبر نیست.');
            }
        });
    }

    protected function prepareForValidation(): void
    {
        // The OTP category only exists on OTP sends; anything posted alongside another type is dropped.
        if ($this->input('service_type') !== 'otp' && $this->has('otp_category')) {
            $this->merge(['otp_category' => null]);
        }

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
