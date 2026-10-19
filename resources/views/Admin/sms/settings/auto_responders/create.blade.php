@extends('Admin.master')

@section('title', 'قانون جدید پاسخ‌گوی خودکار')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1">قانون جدید پاسخ‌گوی خودکار</h3>
            <p class="text-muted small mb-0">کسب‌وکار: {{ $business->title }}</p>
        </div>
        <a href="{{ route('admin.sms.settings.auto-responders.index', auth()->user()?->isSuperAdmin() ? ['business_id' => $business->id] : []) }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa fa-arrow-right me-1"></i> بازگشت به فهرست
        </a>
    </div>

    @include('Admin.sms.settings.navigation')

    <form action="{{ route('admin.sms.settings.auto-responders.store') }}" method="POST">
        @csrf
        @include('Admin.sms.settings.auto_responders._form')
    </form>
</div>
@endsection
