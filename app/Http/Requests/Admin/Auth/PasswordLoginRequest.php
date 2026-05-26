<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Auth;
use App\DTOs\Auth\PasswordCredentialsDTO;
use App\Helpers\Helper;
use Illuminate\Foundation\Http\FormRequest;

class PasswordLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'phone'    => ['required', 'string', 'regex:/^09[0-9]{9}$/', 'digits:11'],
            'password' => ['required', 'string'],
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
            'password.required' => 'وارد کردن رمز عبور الزامی است.',
        ];
    }

    public function toDTO(): PasswordCredentialsDTO
    {
        return new PasswordCredentialsDTO(
            phone:    $this->validated('phone'),
            password: $this->validated('password'),
        );
    }

}
