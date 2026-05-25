<?php

namespace App\Http\Requests\Admin\Auth;

use App\DTOs\Auth\OtpVerificationDTO;
use App\Helpers\Helper;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request برای verify کردن کد یکبار مصرف.
 *
 * طول کد بر اساس config('admin_auth.otp.length') validate می‌شود
 * تا اگر روزی طول کد تغییر کرد، validation هم خودکار همراه شود.
 */
class VerifyOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $codeLength = (int) config('admin_auth.otp.length', 6);

        return [
            'phone' => ['required', 'string', 'regex:/^09[0-9]{9}$/', 'digits:11'],
            'code'  => ['required', 'string', "digits:{$codeLength}"],
        ];
    }

    /**
     * تبدیل اعداد فارسی/عربی به انگلیسی برای phone و code.
     * (کاربر معمولاً کد را با کیبورد فارسی وارد می‌کند.)
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'phone' => Helper::numberConverter((string) $this->input('phone', '')),
            'code'  => Helper::numberConverter((string) $this->input('code', '')),
        ]);
    }

    public function messages(): array
    {
        $codeLength = (int) config('admin_auth.otp.length', 6);

        return [
            'phone.required' => 'وارد کردن شماره موبایل الزامی است.',
            'phone.regex'    => 'فرمت شماره موبایل صحیح نیست.',
            'phone.digits'   => 'شماره موبایل باید ۱۱ رقم باشد.',
            'code.required'  => 'وارد کردن کد یکبار مصرف الزامی است.',
            'code.digits'    => "کد یکبار مصرف باید {$codeLength} رقم باشد.",
        ];
    }

    public function toDTO(): OtpVerificationDTO
    {
        return new OtpVerificationDTO(
            phone: $this->validated('phone'),
            code:  $this->validated('code'),
        );
    }
}
