@extends('Admin.master')

@section('title', 'تعهدنامه استفاده از سرویس پیامک')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1">تعهدنامه استفاده از سرویس پیامک</h3>
            <p class="text-muted small mb-0">
                کسب‌وکار: <strong>{{ $business->title }}</strong> — استفاده از سرویس پیامک تنها پس از مطالعه و تایید
                این تعهدنامه ممکن است.
            </p>
        </div>
    </div>

    @if(session('warning'))
        <div class="alert alert-warning">{{ session('warning') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="fw-bold mb-0"><i class="fa fa-file-text-o me-1"></i> متن تعهدنامه</h5>
                </div>
                <div class="card-body" style="max-height: 420px; overflow-y: auto;">
                    <p style="white-space: pre-wrap;">{{ $contractText }}</p>
                </div>
            </div>

            @if($isSigned)
                <div class="card border-0 shadow-sm border-start border-4 border-success">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; min-width: 48px;">
                                <i class="fa fa-check fs-4"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold text-success mb-1">این تعهدنامه با موفقیت امضا و ثبت نهایی گردیده است</h5>
                                <p class="text-muted small mb-0">سند حاضر به صورت الکترونیکی تایید شده و اکنون در وضعیت <strong>صرفاً قابل مشاهده (Read-Only)</strong> قرار دارد.</p>
                            </div>
                        </div>

                        <div class="row g-3 p-3 bg-light rounded-3 mb-3 border">
                            <div class="col-12 col-md-4">
                                <span class="text-muted small d-block">نام و نام خانوادگی امضاکننده:</span>
                                <strong class="text-dark">{{ $business->sms_contract_signature_name ?: 'ثبت شده' }}</strong>
                            </div>
                            <div class="col-12 col-md-4">
                                <span class="text-muted small d-block">تاریخ و زمان ثبت امضا:</span>
                                <strong class="text-dark">{{ $business->sms_contract_accepted_at ? verta($business->sms_contract_accepted_at)->format('Y/m/d - H:i:s') : '—' }}</strong>
                            </div>
                            <div class="col-12 col-md-4">
                                <span class="text-muted small d-block">شناسه یکتای ثبت سند:</span>
                                <strong class="font-monospace text-primary">SMS-AGR-{{ $business->id }}-{{ $business->sms_contract_accepted_at ? \Carbon\Carbon::parse($business->sms_contract_accepted_at)->timestamp : '0' }}</strong>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                                <i class="fa fa-print ms-1" style="margin-left: 6px !important; margin-right: 0 !important;"></i> چاپ تعهدنامه
                            </button>
                            <a href="{{ route('admin.sms.settings.contracts.index') }}" class="btn btn-outline-primary btn-sm">
                                <i class="fa fa-arrow-right ms-1" style="margin-left: 6px !important; margin-right: 0 !important;"></i> بازگشت به تنظیمات قراردادها
                            </a>
                        </div>
                    </div>
                </div>
            @else
                <form action="{{ route('admin.sms.contract.accept') }}" method="POST">
                    @csrf
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="agree" name="agree" value="1" required>
                                <label class="form-check-label" for="agree">
                                    تعهدنامه فوق و ضوابط و مقررات ارسال پیامک را مطالعه نموده و با مفاد آن موافقم.
                                </label>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">نام و نام خانوادگی (امضای الکترونیکی) <span class="text-danger">*</span></label>
                                <input type="text" name="signature_name" class="form-control" placeholder="نام و نام خانوادگی خود را به‌عنوان تایید امضا وارد کنید" required>
                                <div class="form-text text-muted small">
                                    ثبت نام و نام خانوادگی در این فیلد و ارسال فرم، به منزله امضای الکترونیکی تعهدنامه است.
                                </div>
                            </div>
                            <button type="submit" class="btn btn-success px-4">
                                <i class="fa fa-check me-1"></i> تایید و امضای تعهدنامه
                            </button>
                        </div>
                    </div>
                </form>
            @endif
        </div>

        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-2 px-3">
                    <small class="fw-bold text-muted"><i class="fa fa-balance-scale me-1"></i> ضوابط و مقررات ارسال پیامک</small>
                </div>
                <div class="card-body p-3" style="max-height: 420px; overflow-y: auto;">
                    <p class="small text-muted mb-0" style="white-space: pre-wrap;">{{ $regulatoryRulesText }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
