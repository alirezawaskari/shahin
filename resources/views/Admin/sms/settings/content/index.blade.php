@extends('Admin.master')

@section('title', 'متون حقوقی و ضوابط پیامک')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1">متون حقوقی و ضوابط پیامک</h3>
            <p class="text-muted small mb-0">
                ویرایش متن ضوابط و مقررات مخابراتی و متن تعهدنامه استفاده از سرویس پیامک. این متون در صفحه تعهدنامه
                و به‌صورت پنل کناری در صفحات ارسال تکی و گروهی نمایش داده می‌شوند.
            </p>
        </div>
    </div>

    @include('Admin.sms.settings.navigation')

    <div class="alert alert-warning">
        <i class="fa fa-exclamation-triangle me-1"></i>
        <strong>توجه:</strong> متن نهایی حقوقی/مقرراتی هنوز از سوی کارفرما ارائه نشده است. تا قبل از درج متن واقعی،
        یک پیام «تنظیم نشده» به کاربران نمایش داده می‌شود؛ لطفاً متن رسمی را پس از دریافت از کارفرما در همین صفحه
        ثبت نمایید.
    </div>

    @if($errors->any())
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.sms.settings.content.update') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold mb-0">متن ضوابط و مقررات مخابراتی (محدودیت‌های محتوای پیامک)</h5>
            </div>
            <div class="card-body">
                <textarea name="regulatory_rules_text" class="form-control" rows="10"
                    placeholder="متن ضوابط و مقررات مخابراتی را اینجا وارد کنید...">{{ old('regulatory_rules_text', $regulatoryRulesText) }}</textarea>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold mb-0">متن تعهدنامه استفاده از سرویس پیامک</h5>
            </div>
            <div class="card-body">
                <textarea name="contract_text" class="form-control" rows="10"
                    placeholder="متن تعهدنامه را اینجا وارد کنید...">{{ old('contract_text', $contractText) }}</textarea>
            </div>
        </div>

        <div class="text-end">
            <button type="submit" class="btn btn-primary px-4">
                <i class="fa fa-check me-1"></i> ذخیره متون
            </button>
        </div>
    </form>
</div>
@endsection
