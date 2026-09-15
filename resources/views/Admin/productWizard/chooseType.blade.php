@extends('Admin.master')

@section('title','ویزارد ایجاد محصول')

@section('content')

    <div class="page-header">
        <div>
            <h3>ویزارد ایجاد محصول</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">داشبورد</a></li>
                    <li class="breadcrumb-item active" aria-current="page">ویزارد ایجاد محصول</li>
                </ol>
            </nav>
        </div>
    </div>

    @include('Admin.productWizard.partials.steps',['current'=>'type','kind'=>null])

    <div class="card">
        <div class="card-body">
            <h5 class="mb-1">قصد ایجاد کدام مورد را دارید؟</h5>
            <p class="text-muted">کسب‌وکار شما هم کالا و هم خدمت دارد؛ لطفا نوع مورد نظر را انتخاب کنید.</p>

            <div class="row mt-4">
                <div class="col-12 col-md-6 mb-3">
                    <a href="{{ route('productWizard.category','product') }}" class="text-decoration-none">
                        <div class="card h-100 border">
                            <div class="card-body text-center py-5">
                                <i class="icon ti-package" style="font-size:34px"></i>
                                <h5 class="mt-3 mb-1">کالا</h5>
                                <p class="text-muted mb-0">ایجاد دسته‌بندی کالا، ثبت کالا و سپس ثبت تنوع</p>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col-12 col-md-6 mb-3">
                    <a href="{{ route('productWizard.category','service') }}" class="text-decoration-none">
                        <div class="card h-100 border">
                            <div class="card-body text-center py-5">
                                <i class="icon ti-briefcase" style="font-size:34px"></i>
                                <h5 class="mt-3 mb-1">خدمت</h5>
                                <p class="text-muted mb-0">ایجاد دسته‌بندی خدمات و ثبت خدمت</p>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
@stop
