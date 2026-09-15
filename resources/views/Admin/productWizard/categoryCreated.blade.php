@extends('Admin.master')

@section('title','ویزارد ایجاد محصول')

@section('content')

    <div class="page-header">
        <div>
            <h3>ویزارد ایجاد محصول</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">داشبورد</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('productWizard.index') }}">ویزارد ایجاد محصول</a></li>
                    <li class="breadcrumb-item active" aria-current="page">دسته‌بندی ایجاد شد</li>
                </ol>
            </nav>
        </div>
    </div>

    @include('Admin.productWizard.partials.steps',['current'=>'category','kind'=>$kind])

    <div class="card">
        <div class="card-body">
            <div class="alert alert-success" role="alert">
                دسته‌بندی «{{ $category->title }}» با موفقیت ایجاد شد.
            </div>

            <p class="text-muted">
                مسیر دسته‌بندی: {{ \App\Models\Category::findPath($category->id) }} / {{ $category->title }}
            </p>

            @if($skip_url)
                <p>
                    قدم بعد تعریف ویژگی‌های این دسته‌بندی است (مثل رنگ و سایز). این ویژگی‌ها یک بار
                    اینجا تعریف می‌شوند و بعد سرِ ثبت هر کالا فقط مقدارشان را انتخاب می‌کنید.
                </p>
            @else
                <p>برای ادامه، {{ $label }} خود را در همین دسته‌بندی ثبت کنید. دسته‌بندی به‌صورت خودکار انتخاب شده است.</p>
            @endif

            <div class="mt-4">
                @if($skip_url)
                    <a href="{{ $next_url }}" class="btn btn-success">تعریف ویژگی‌های این دسته‌بندی</a>
                    <a href="{{ $skip_url }}" class="btn btn-outline-success">رد کردن و ثبت {{ $label }}</a>
                @else
                    <a href="{{ $next_url }}" class="btn btn-success">ثبت {{ $label }} در این دسته‌بندی</a>
                @endif
                <a href="{{ $back_url }}" class="btn btn-outline-primary">بازگشت به لیست دسته‌بندی‌ها</a>
                <a href="{{ route('category.show',[$category->id,'wizard'=>1]) }}" class="btn btn-outline-secondary">
                    افزودن زیرمجموعه برای این دسته‌بندی
                </a>
            </div>
        </div>
    </div>
@stop
