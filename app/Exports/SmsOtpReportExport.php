<?php

namespace App\Exports;

use App\Models\User;
use App\Services\Sms\CatalogBreadcrumbResolver;
use App\Services\Sms\SmsConfidentialityGuard;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SmsOtpReportExport implements FromCollection, ShouldAutoSize, WithHeadings
{
    use Exportable;

    public function __construct(
        protected Builder $query,
        protected ?User $viewer = null
    ) {
        $this->viewer = $this->viewer ?? auth()->user();
    }

    public function collection(): Collection
    {
        $messages = $this->query->get();
        // One batched lookup for every row's request -> "Category > Subcategory > Service" label.
        app(CatalogBreadcrumbResolver::class)->annotateMessages($messages);

        return $messages->map(function ($message) {
            $date = $message->created_at;
            if (function_exists('verta')) {
                $dateStr = verta($date)->format('Y/m/d H:i:s');
            } else {
                $dateStr = $date?->format('Y-m-d H:i:s') ?? '';
            }

            return [
                'id' => $message->id,
                'direction' => \App\Support\SmsLabels::direction($message->direction),
                'sender_number' => $message->senderNumber?->number ?? $message->sender_phone ?? '-',
                'recipient_phone' => $message->recipient_phone,
                'content' => SmsConfidentialityGuard::visibleContent($message, $this->viewer) ?? '*** محرمانه - دسترسی محدود ***',
                'request' => $message->request_breadcrumb ?: '-',
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
            'جهت',
            'شماره فرستنده',
            'شماره گیرنده',
            'متن پیام',
            'درخواست',
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
