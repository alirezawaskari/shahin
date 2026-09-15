@extends('Admin.master')

@section('title','ویژگی‌های دسته‌بندی')

@section('content')

    <div class="page-header">
        <div>
            <h3>ویژگی‌های دسته‌بندی «{{ $category->title }}»</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">داشبورد</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('productWizard.index') }}">ویزارد ایجاد محصول</a></li>
                    <li class="breadcrumb-item active" aria-current="page">ویژگی‌های دسته‌بندی</li>
                </ol>
            </nav>
        </div>
    </div>

    @include('Admin.productWizard.partials.steps',['current'=>'attributes','kind'=>'product'])

    <div class="card mb-3">
        <div class="card-body">
            <p class="mb-2">
                ویژگی‌ها یک بار روی همین دسته‌بندی تعریف می‌شوند و بعد هر کالایی که در آن ثبت کنید،
                همین ویژگی‌ها را می‌بیند و فقط مقدارشان را انتخاب می‌کند.
            </p>
            <p class="text-muted mb-0">
                مثال: دسته‌بندی «مانتو» ویژگی‌های «رنگ» و «سایز» دارد؛ کالایی که ثبت می‌کنید مقدار
                «رنگ = سبز» می‌گیرد. ویژگی‌ای که «قابلیت قیمت‌گذاری» داشته باشد، در قدم بعد محور
                تنوع کالا می‌شود (هر رنگ یک قیمت و موجودی).
            </p>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
                <h5 class="mb-0">گروه‌های ویژگی این دسته‌بندی</h5>
                <a href="{{ route('attributeGroup.createByCategory',[$category->id,'wizard'=>1]) }}"
                   class="btn btn-sm btn-success">افزودن گروه ویژگی</a>
            </div>

            @if($attribute_groups->isEmpty())
                <div class="alert alert-warning mb-0">
                    برای این دسته‌بندی هنوز گروه ویژگی‌ای تعریف نشده است. اگر کالاهای این دسته‌بندی
                    ویژگی خاصی ندارند می‌توانید این مرحله را رد کنید و مستقیم کالا را ثبت کنید.
                </div>
            @else
                <table class="table table-bordered table-striped">
                    <thead>
                    <tr>
                        <th>گروه ویژگی</th>
                        <th>ویژگی</th>
                        <th>نوع</th>
                        <th>مقادیر</th>
                        <th>اقدام</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($attribute_groups as $group)
                        @if($group->attributes->isEmpty())
                            <tr>
                                <td>{{ $group->title }}</td>
                                <td colspan="3" class="text-muted">هنوز ویژگی‌ای ندارد</td>
                                <td>
                                    <a href="{{ route('createAttribute',[$group->id,'wizard'=>1,'wizard_category'=>$category->id]) }}"
                                       class="btn btn-sm btn-outline-success">افزودن ویژگی</a>
                                </td>
                            </tr>
                        @else
                            @foreach($group->attributes as $attribute)
                                <tr>
                                    @if($loop->first)
                                        <td rowspan="{{ $group->attributes->count() }}">{{ $group->title }}</td>
                                    @endif
                                    <td>
                                        {{ $attribute->title }}
                                        @if($attribute->is_price)
                                            <span class="badge bg-primary">محور تنوع</span>
                                        @endif
                                    </td>
                                    <td>{{ $attribute->type }}</td>
                                    <td>
                                        @forelse($attribute->attributeItems as $item)
                                            <span class="badge bg-secondary">{{ $item->title }}</span>
                                        @empty
                                            <span class="text-danger">بدون مقدار</span>
                                        @endforelse
                                    </td>
                                    <td>
                                        <a href="{{ route('createAttributeItem',[$attribute->id,'wizard'=>1,'wizard_category'=>$category->id]) }}"
                                           class="btn btn-sm btn-outline-primary">افزودن مقدار</a>
                                        @if($loop->first)
                                            <a href="{{ route('createAttribute',[$group->id,'wizard'=>1,'wizard_category'=>$category->id]) }}"
                                               class="btn btn-sm btn-outline-success">افزودن ویژگی</a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    @endforeach
                    </tbody>
                </table>
            @endif

            <hr/>
            <div>
                <a href="{{ $next_url }}" class="btn btn-success">
                    {{ $attribute_groups->isEmpty() ? 'رد کردن و ثبت کالا' : 'ادامه و ثبت کالا در این دسته‌بندی' }}
                </a>
                <a href="{{ $back_url }}" class="btn btn-outline-secondary">بازگشت به دسته‌بندی‌ها</a>
            </div>
        </div>
    </div>
@stop
