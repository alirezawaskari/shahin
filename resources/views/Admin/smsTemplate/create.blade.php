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
                    <label>متن قالب</label>
                    <textarea class="form-control" name="content"></textarea>
                </div>
                <div class="col-12 p-4 alert-warning mb-2">
                    <p>ملاحضات : </p>
                    <p>1 - قالب ها حتما باید دارای متغیر نام مجموعه باشند.</p>
                    <p>2 - همه متغیرهارو به صورت %param1% , %param2% , .... داخل متن درج کنید.</p>
                    <p>3 - توجه داشته باشید که عبارت لغو11 حتما باید انتهای تمام قالب ها درج شود.</p>
                    <p>4 - برای درج خودکار اطلاعات فرستنده بر اساس نقش کاربر، از این متغیرها استفاده کنید:
                        {company_name} (نام مجموعه) ، {phone} (تلفن) ، {address} (آدرس). هر کدام لازم بود درج کنید.</p>
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
@endsection
