@extends('Admin.master')

@section('title', 'جزئیات پیامک')

@section('content')
@php
    $isInbound = $smsMessage->direction === 'inbound';
    $statusLabel = \App\Support\SmsLabels::status($smsMessage->status);
    $serviceLabel = \App\Support\SmsLabels::serviceType($smsMessage->service_type);
    $campaign = $smsMessage->campaign;
    $campaignRoute = null;
    if ($campaign) {
        $campaignRoute = $campaign->type === 'ai' ? 'admin.sms.ai.campaigns.show' : 'admin.sms.bulk.campaigns.show';
        $campaignRoute = Route::has($campaignRoute) ? route($campaignRoute, $campaign) : null;
    }
    $fmt = fn ($d) => $d ? verta($d)->format('Y/m/d H:i:s') : '-';
@endphp
<div class="container-fluid py-3">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1">جزئیات پیامک #{{ $smsMessage->id }}</h3>
            <p class="text-muted small mb-0">نمایش فقط‌خواندنی اطلاعات کامل این پیامک</p>
        </div>
        <a href="{{ route('admin.sms.logs.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa fa-arrow-right me-1"></i> بازگشت به لاگ پیامک‌ها
        </a>
    </div>

    @include('Admin.sms.logs.navigation')

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-6 col-md-3 mb-2"><strong>جهت:</strong> {{ \App\Support\SmsLabels::direction($smsMessage->direction) }}</div>
                <div class="col-6 col-md-3 mb-2">
                    <strong>نوع:</strong> {{ $serviceLabel }}
                    @if($smsMessage->service_type === 'otp')
                        <span class="badge badge-light border">{{ \App\Enums\SmsOtpCategory::labelFor($smsMessage->otp_category) }}</span>
                    @endif
                    @if($smsMessage->is_bulk)
                        <span class="badge badge-dark">انبوه</span>
                    @endif
                </div>
                <div class="col-6 col-md-3 mb-2"><strong>وضعیت:</strong> {{ $statusLabel }}</div>
                <div class="col-6 col-md-3 mb-2"><strong>وضعیت تحویل:</strong> {{ \App\Support\SmsLabels::status($smsMessage->delivery_status) }}</div>
            </div>
            <div class="row mb-3">
                <div class="col-6 col-md-3 mb-2"><strong>فرستنده:</strong> <span style="direction: ltr; display: inline-block;">{{ $smsMessage->sender_phone ?: ($smsMessage->senderNumber?->number ?: '-') }}</span></div>
                <div class="col-6 col-md-3 mb-2"><strong>گیرنده:</strong> <span style="direction: ltr; display: inline-block;">{{ $smsMessage->recipient_phone ?: '-' }}</span></div>
                <div class="col-6 col-md-3 mb-2"><strong>کسب‌وکار:</strong> {{ $smsMessage->business?->title ?: '-' }}</div>
                <div class="col-6 col-md-3 mb-2"><strong>گروه پیام:</strong> {{ $smsMessage->group?->name ?: '-' }}</div>
            </div>
            <div class="row mb-3">
                <div class="col-6 col-md-3 mb-2"><strong>تعداد صفحات:</strong> {{ $smsMessage->page_count ?? '-' }}</div>
                <div class="col-6 col-md-3 mb-2"><strong>تعداد کاراکتر:</strong> {{ $smsMessage->character_count ?? '-' }}</div>
                <div class="col-6 col-md-3 mb-2"><strong>هزینه:</strong> {{ number_format($smsMessage->cost ?? 0) }} ریال</div>
                <div class="col-6 col-md-3 mb-2"><strong>شناسه رهگیری:</strong> <span style="direction: ltr; display: inline-block; font-family: monospace;">{{ $smsMessage->tracking_id ?: '-' }}</span></div>
            </div>
            <div class="row mb-3">
                <div class="col-6 col-md-3 mb-2"><strong>تاریخ ثبت:</strong> {{ $fmt($smsMessage->created_at) }}</div>
                <div class="col-6 col-md-3 mb-2"><strong>آخرین بروزرسانی:</strong> {{ $fmt($smsMessage->updated_at) }}</div>
                <div class="col-6 col-md-3 mb-2"><strong>زمان تحویل:</strong> {{ $fmt($smsMessage->delivered_at) }}</div>
                <div class="col-6 col-md-3 mb-2">
                    <strong>کمپین:</strong>
                    @if($campaign && $campaignRoute)
                        <a href="{{ $campaignRoute }}">کمپین #{{ $campaign->id }}</a>
                    @elseif($campaign)
                        کمپین #{{ $campaign->id }}
                    @else
                        -
                    @endif
                </div>
            </div>
            @if($smsMessage->request_breadcrumb)
                <div class="mb-3"><strong>درخواست مرتبط:</strong> {{ $smsMessage->request_breadcrumb }}</div>
            @endif
            @if($smsMessage->error_reason)
                <div class="alert alert-danger"><strong>علت خطا:</strong> {{ $smsMessage->error_reason }}</div>
            @endif
            <div>
                <label class="fw-bold">متن پیامک:</label>
                <div class="p-3 bg-light rounded border text-right" style="white-space: pre-wrap;">@if($smsMessage->content_redacted ?? false)محرمانه - دسترسی محدود@else{{ $smsMessage->content }}@endif</div>
            </div>
        </div>
    </div>
</div>
@endsection
