<?php

namespace App\Http\Requests\Admin\Sms;

use Illuminate\Foundation\Http\FormRequest;

class PreviewBulkSmsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'recipient_type' => ['nullable', 'string', 'in:normal,advertising'],
            'message' => ['nullable', 'string'],
            'sms_message_template_id' => ['nullable', 'integer', 'exists:sms_message_templates,id'],
            'sender_number_id' => ['nullable', 'integer', 'exists:sender_numbers,id'],
            'filters' => ['nullable', 'array'],
            'custom_numbers' => ['nullable', 'string'],
        ];
    }
}
