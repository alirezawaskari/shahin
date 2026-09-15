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
                    <li class="breadcrumb-item active" aria-current="page">اتمام فرآیند</li>
                </ol>
            </nav>
        </div>
    </div>

    @include('Admin.productWizard.partials.steps',['current'=>'done','kind'=>$kind])

    <div class="card">
        <div class="card-body">
            <div class="alert alert-success" role="alert">
                فرآیند ایجاد با موفقیت به پایان رسید.
                @if($product) {{ $label }} «{{ $product->name }}» ثبت شد. @endif
            </div>

            <div class="mt-4">
                <a href="{{ $list_url }}" class="btn btn-primary">
                    بازگشت به لیست {{ $kind === 'service' ? 'خدمات' : 'کالاها' }}
                </a>
                @if($product && $kind !== 'service')
                    <a href="/dashboard/product/pricesList/{{ $product->id }}" class="btn btn-outline-primary">
                        مشاهده تنوع‌های این کالا
                    </a>
                    <a href="/dashboard/product/price/{{ $product->id }}?wizard=1" class="btn btn-outline-secondary">
                        ثبت تنوع دیگر
                    </a>
                @endif
                <a href="{{ $again_url }}" class="btn btn-outline-success">ایجاد مورد جدید</a>
            </div>
        </div>
    </div>
@stop
