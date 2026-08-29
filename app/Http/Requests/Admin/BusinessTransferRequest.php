<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BusinessTransferRequest extends FormRequest
{
    /**
     * Enforce the independent permission on the write request itself. Hiding the button
     * must never make a hand-written POST an authorization bypass.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('transfer_to_business') === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'entity' => ['required', 'string', Rule::in([
                'factor',
                'factor_item',
                'user',
                'employee',
                'callcenter',
            ])],
            'ids' => ['required', 'array', 'min:1', 'max:1000'],
            'ids.*' => ['required', 'integer', 'distinct', 'min:1'],
            'destination_business_id' => [
                'required',
                'integer',
                Rule::exists('ghorfe_online_lists', 'id')->where(function ($query) {
                    $query->where('status', 1)->whereNull('deleted_at');
                }),
            ],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'entity.in' => 'نوع رکورد انتخاب شده برای انتقال معتبر نیست.',
            'ids.required' => 'حداقل یک رکورد را برای انتقال انتخاب کنید.',
            'ids.min' => 'حداقل یک رکورد را برای انتقال انتخاب کنید.',
            'ids.max' => 'در هر انتقال حداکثر ۱۰۰۰ رکورد قابل انتخاب است.',
            'ids.*.distinct' => 'رکوردهای تکراری در انتخاب وجود دارد.',
            'destination_business_id.required' => 'کسب و کار مقصد را انتخاب کنید.',
            'destination_business_id.exists' => 'کسب و کار مقصد معتبر یا فعال نیست.',
        ];
    }
}
