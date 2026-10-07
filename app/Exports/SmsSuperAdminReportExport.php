<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SmsSuperAdminReportExport implements FromCollection, ShouldAutoSize, WithHeadings
{
    use Exportable;

    public function __construct(
        protected Builder $query
    ) {
    }

    public function collection(): Collection
    {
        return $this->query->get()->map(function ($message) {
            $date = $message->created_at;
            if (function_exists('verta')) {
                $dateStr = verta($date)->format('Y/m/d H:i:s');
            } else {
                $dateStr = $date?->format('Y-m-d H:i:s') ?? '';
            }

            return [
                'id' => $message->id,
                'business' => $message->business?->title ?? '-',
                'service_type' => match ($message->service_type) {
                    'otp' => 'اعتبارسنجی (OTP)',
                    'advertising' => 'تبلیغاتی',
                    'normal' => 'عادی',
                    default => $message->service_type ?? '-',
                },
                'sender_number' => $message->senderNumber?->number ?? $message->sender_phone ?? '-',
                'recipient_phone' => $message->recipient_phone,
                'sms_panel' => $message->senderNumber?->sms_panel ?? '-',
                'content' => $message->content,
                'character_count' => $message->character_count,
                'page_count' => $message->page_count,
                'cost' => $message->cost ? number_format((float) $message->cost) : '0',
                'status' => match ($message->status) {
                    'delivered' => 'تحویل شده',
                    'sent' => 'ارسال شده',
                    'queued' => 'در صف ارسال',
                    'failed' => 'ناموفق',
                    default => $message->status ?? '-',
                },
                'tracking_id' => $message->tracking_id ?? '-',
                'template' => $message->template?->title ?? '-',
                'group' => $message->group?->name ?? '-',
                'created_at' => $dateStr,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'شناسه',
            'کسب‌وکار',
            'نوع سرویس',
            'شماره فرستنده',
            'شماره گیرنده',
            'پنل پیامک',
            'متن پیام',
            'تعداد کاراکتر',
            'تعداد صفحه',
            'هزینه (ریال)',
            'وضعیت',
            'شناسه رهگیری',
            'الگو',
            'گروه',
            'تاریخ ثبت',
        ];
    }
}
