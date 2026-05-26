<?php

namespace App\Http\Requests\Admin\Auth;

use App\Helpers\Helper;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request برای درخواست ارسال کد یکبار مصرف.
 *
 * فقط شماره موبایل را اعتبارسنجی می‌کند؛ Service مسئول چک کردن
 * ادمین بودن و سایر منطق‌هاست.
 */
class RequestOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone' => ['required', 'string', 'regex:/^09[0-9]{9}$/', 'digits:11'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'phone' => Helper::numberConverter((string) $this->input('phone', '')),
        ]);
    }

    public function messages(): array
    {
        return [
            'phone.required' => 'وارد کردن شماره موبایل الزامی است.',
            'phone.regex'    => 'فرمت شماره موبایل صحیح نیست (باید با ۰۹ شروع شود).',
            'phone.digits'   => 'شماره موبایل باید ۱۱ رقم باشد.',
        ];
    }
}
