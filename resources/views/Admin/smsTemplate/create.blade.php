@extends('Admin.master')
@section('title')ایجاد قالب پیامک@endsection

@section('content')
<div class="page-header">
    <div>
        <h3>ایجاد قالب پیامک جدید</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                <li class="breadcrumb-item active" aria-current="page"><a href="{{ route('smsTemplate.index') }}">فهرست
                        قالب های پیامکی</a></li>
                <li class="breadcrumb-item active" aria-current="page">ایجاد قالب پیامک جدید</li>
            </ol>
        </nav>
    </div>
    <div>
        <a class="btn btn-primary" href="{{ route('smsTemplate.index') }}">فهرست قالب های پیامکی</a>
    </div>
</div>
<div class="card">
    <div class="card-body">
        <form class="form-horizontal" role="form" method="post" action="{{route('smsTemplate.store')}}"
            enctype="multipart/form-data">
            @csrf
            @include('Admin.layouts.errors')
            <div class="row">
                <div class="col-12 col-md-6 mb-2">
                    <label>نام قالب</label>
                    <input type="text" class="form-control" name="fa_name" required>
                </div>
                <div class="col-12 col-md-6 mb-2">
                    <label>کلمه کلیدی قالب</label>
                    <input type="text" class="form-control" name="name" required>
                </div>
                <div class="col-12 col-md-6 mb-2">
                    <label>وضعیت</label>
                    <select class="form-control" name="status">
                        <option value="0">غیرفعال</option>
                        <option value="1">فعال</option>
                    </select>
                </div>
                <div class="col-12 mb-2">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="mb-0">متن قالب</label>
                        <small class="text-muted">روی متغیرهای زیر کلیک کنید تا در متن درج شوند:</small>
                    </div>
                    <div class="mb-2 d-flex flex-wrap gap-1">
                        <button type="button" class="btn btn-sm btn-outline-primary insert-param me-1 mb-1" data-param="{نام کاربر}">{نام کاربر}</button>
                        <button type="button" class="btn btn-sm btn-outline-primary insert-param me-1 mb-1" data-param="{نام خانوادگی}">{نام خانوادگی}</button>
                        <button type="button" class="btn btn-sm btn-outline-primary insert-param me-1 mb-1" data-param="{نام فروشنده}">{نام فروشنده}</button>
                        <button type="button" class="btn btn-sm btn-outline-primary insert-param me-1 mb-1" data-param="{تاریخ}">{تاریخ}</button>
                        <button type="button" class="btn btn-sm btn-outline-primary insert-param me-1 mb-1" data-param="{ساعت}">{ساعت}</button>
                        <button type="button" class="btn btn-sm btn-outline-primary insert-param me-1 mb-1" data-param="{شناسه فاکتور}">{شناسه فاکتور}</button>
                        <button type="button" class="btn btn-sm btn-outline-primary insert-param me-1 mb-1" data-param="{لینک پرداخت}">{لینک پرداخت}</button>
                        <button type="button" class="btn btn-sm btn-outline-primary insert-param me-1 mb-1" data-param="{شناسه درخواست}">{شناسه درخواست}</button>
                    </div>
                    <textarea class="form-control" rows="15" name="content" id="templateContent"></textarea>
                </div>
                <div class="col-12 p-4 alert-warning mb-2">
                    <p><strong>ملاحظات و راهنما:</strong></p>
                    <p>۱ - برای شخصی‌سازی پیامک، می‌توانید از متغیرهای دینامیک فوق استفاده کنید تا هنگام ارسال با اطلاعات واقعی جایگزین شوند.</p>
                    <p>۲ - برای درج خودکار اطلاعات فرستنده: {company_name} (نام مجموعه) ، {phone} (تلفن) ، {address} (آدرس).</p>
                    <p>۳ - طبق قوانین اپراتوری، در پیام‌های تبلیغاتی عبارت «لغو 11» به انتهای پیام افزوده خواهد شد.</p>
                </div>
            </div>

            <div class="col-12">
                <input type="submit" class="btn btn-success border-0" value="ثبت">
            </div>
        </form>
    </div>
</div>
@stop
@section('js')
<script src="{{ asset('/assets/dashboard/js/layouts.js') }}"></script>
<script src="{{ asset('/assets/dashboard/js/smsTemplate.js') }}"></script>
<script>
    $(document).ready(function() {
        $(document).on('click', '.insert-param', function() {
            var param = $(this).data('param');
            var $txt = $('#templateContent');
            var pos = $txt.prop('selectionStart') || $txt.val().length;
            var val = $txt.val();
            $txt.val(val.substring(0, pos) + param + val.substring(pos)).focus();
        });
    });
</script>
@endsection
