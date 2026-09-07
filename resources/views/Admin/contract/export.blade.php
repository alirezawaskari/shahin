<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>خروجی قرارداد - {{ $contract->title }}</title>
    <style>
        body {
            font-family: Tahoma, "IRANSans", sans-serif;
            direction: rtl;
            background: #f3f4f6;
            color: #111;
            margin: 0;
            padding: 24px;
            font-size: 13px;
            line-height: 1.9;
        }

        .sheet {
            background: #fff;
            max-width: 880px;
            margin: 0 auto;
            padding: 32px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
        }

        h1 { font-size: 20px; margin: 0 0 4px; }
        h2 { font-size: 15px; margin: 24px 0 8px; padding-bottom: 6px; border-bottom: 1px solid #e5e7eb; }

        .muted { color: #6b7280; font-size: 12px; }

        table.info { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        table.info th, table.info td {
            border: 1px solid #e5e7eb;
            padding: 7px 10px;
            text-align: right;
            vertical-align: top;
        }
        table.info th { background: #f9fafb; width: 180px; font-weight: normal; color: #374151; }

        .contract-body {
            border: 1px solid #e5e7eb;
            padding: 16px;
            border-radius: 4px;
            background: #fcfcfc;
        }
        .contract-body img { max-width: 100%; }

        .actions { max-width: 880px; margin: 0 auto 16px; }
        .actions a, .actions button {
            font-family: inherit;
            font-size: 13px;
            padding: 7px 14px;
            border-radius: 4px;
            border: 1px solid transparent;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn-print { background: #2563eb; color: #fff; }
        .btn-pdf { background: #059669; color: #fff; }
        .btn-file { background: #4b5563; color: #fff; }

        .signatures { display: flex; gap: 24px; margin-top: 40px; }
        .signatures div { flex: 1; border-top: 1px dashed #9ca3af; padding-top: 8px; text-align: center; }

        @media print {
            body { background: #fff; padding: 0; }
            .actions { display: none; }
            .sheet { border: 0; max-width: none; padding: 0; }
        }
    </style>
</head>
<body>

<div class="actions">
    <button class="btn-print" onclick="window.print();">چاپ قرارداد</button>
    <button class="btn-pdf" onclick="pdfSection()">ذخیره به صورت PDF</button>
    @if($contract->hasOriginalFile())
        {{-- The original Word/PDF, offered only when one was actually attached. --}}
        <a class="btn-file" href="/dashboard/contract/{{ $contract->id }}/download">دانلود فایل اصلی قرارداد</a>
    @endif
</div>

<div class="sheet" id="pdf-section">

    <h1>{{ $contract->title }}</h1>
    <div class="muted">
        {{ optional($contract->folder)->title ? 'پوشه: ' . $contract->folder->title : 'دسته بندی نشده' }}
        @if($contract->type) — نوع: {{ \App\Models\Contract::TYPE[$contract->type] ?? '' }} @endif
    </div>

    {{-- اطلاعات اصلی قرارداد --}}
    <h2>اطلاعات قرارداد</h2>
    <table class="info">
        <tr>
            <th>شماره قرارداد</th>
            <td>{{ optional($contractUsage)->contract_number ?: '—' }}</td>
        </tr>
        <tr>
            <th>تاریخ قرارداد</th>
            <td>
                {{ optional($contractUsage)->contract_date
                    ? verta($contractUsage->contract_date)->format('Y/m/d')
                    : '—' }}
            </td>
        </tr>
        @if($contractUsage)
            <tr>
                <th>محل استفاده</th>
                <td>{{ $contractUsage->location_label }} ({{ $contractUsage->owner_label }})</td>
            </tr>
        @endif
    </table>

    {{-- طرفین قرارداد --}}
    <h2>طرفین قرارداد</h2>
    <table class="info">
        <tr>
            <th>طرف اول</th>
            <td>{{ config('app.company_name', 'لاله کارت') }}</td>
        </tr>
        <tr>
            <th>طرف دوم</th>
            <td>
                @if($counterparty)
                    {{ trim($counterparty->name . ' ' . $counterparty->family) }}
                    @if($counterparty->identifier_code)
                        <br><span class="muted">کد ملی: {{ $counterparty->identifier_code }}</span>
                    @endif
                    @if($counterparty->phone)
                        <br><span class="muted">تلفن: {{ $counterparty->phone }}</span>
                    @endif
                    @if($counterparty->address)
                        <br><span class="muted">نشانی: {{ $counterparty->address }}</span>
                    @endif
                @else
                    —
                @endif
            </td>
        </tr>
    </table>

    {{-- متن قرارداد --}}
    <h2>متن قرارداد</h2>
    <div class="contract-body">
        {!! $contract->content ?: '<span class="muted">متنی برای این قرارداد ثبت نشده است.</span>' !!}
    </div>

    {{-- اطلاعات خدمت — only when the contract is about a service --}}
    @if($service)
        <h2>اطلاعات خدمت</h2>
        <table class="info">
            <tr>
                <th>عنوان خدمت</th>
                <td>{{ $service->title }}</td>
            </tr>
            @if($service->price)
                <tr>
                    <th>مبلغ خدمت</th>
                    <td>{{ number_format($service->price) }} ریال</td>
                </tr>
            @endif
        </table>
    @endif

    {{-- اطلاعات فاکتور — only when the export was opened from an invoice --}}
    @if($factor)
        <h2>اطلاعات فاکتور</h2>
        <table class="info">
            <tr>
                <th>شماره پیگیری</th>
                <td>{{ $factor->resnumber ?: '—' }}</td>
            </tr>
            <tr>
                <th>تاریخ فاکتور</th>
                <td>{{ $factor->created_at ? verta($factor->created_at)->format('Y/m/d') : '—' }}</td>
            </tr>
            <tr>
                <th>مبلغ کل</th>
                <td>{{ $factor->price ? number_format($factor->price) . ' ریال' : '—' }}</td>
            </tr>
            @if($factorItem)
                <tr>
                    <th>ردیف فاکتور</th>
                    <td>
                        {{ optional($factorItem->factoritemable)->title ?: '—' }}
                        @if($factorItem->resnumber)
                            <span class="muted">({{ $factorItem->resnumber }})</span>
                        @endif
                    </td>
                </tr>
            @endif
        </table>
    @endif

    {{-- اطلاعات همکار — only when the export was opened from the employees section --}}
    @if($employee)
        <h2>اطلاعات همکار</h2>
        <table class="info">
            <tr>
                <th>نام همکار</th>
                <td>{{ optional($employee->user) ? trim($employee->user->name . ' ' . $employee->user->family) : '—' }}</td>
            </tr>
            <tr>
                <th>عنوان شغلی</th>
                <td>{{ $employee->work_title ?: '—' }}</td>
            </tr>
            <tr>
                <th>شماره قرارداد همکار</th>
                <td>{{ $employee->contract_number ?: '—' }}</td>
            </tr>
            <tr>
                <th>تاریخ شروع همکاری</th>
                <td>
                    {{ $employee->contract_start_date
                        ? verta($employee->contract_start_date)->format('Y/m/d')
                        : '—' }}
                </td>
            </tr>
        </table>
    @endif

    <div class="signatures">
        <div>امضای طرف اول</div>
        <div>امضای طرف دوم</div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
    function pdfSection() {
        var element = document.getElementById('pdf-section');
        html2pdf().from(element).save('contract-{{ $contract->id }}.pdf');
    }
</script>
</body>
</html>
