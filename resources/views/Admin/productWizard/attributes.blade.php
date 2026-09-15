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
                        <th>گروه ویژگی / ویژگی</th>
                        <th>نوع</th>
                        <th>مقادیر</th>
                        <th>اقدام</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($attribute_groups as $group)
                        {{-- ردیف خود گروه: همه کارهای گروه در همان منوی «اقدام» --}}
                        <tr>
                            <td colspan="3" class="fw-bold">
                                {{ $group->title }}
                                @if($group->attributes->isEmpty())
                                    <span class="text-muted fw-normal">— هنوز ویژگی‌ای ندارد</span>
                                @endif
                            </td>
                            <td>
                                <div class="dropdown">
                                    <a href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                       class="btn btn-dark btn-floating btn-icon btn-sm">
                                        <i aria-hidden="true" class="fa fa-ellipsis-v"></i>
                                    </a>
                                    <div class="dropdown-menu">
                                        <a href="{{ route('createAttribute',[$group->id,'wizard'=>1,'wizard_category'=>$category->id]) }}">
                                            <button type="button" class="dropdown-item pointer">افزودن ویژگی</button>
                                        </a>
                                        <a href="{{ route('attributeGroup.edit',[$group->id,'wizard'=>1]) }}">
                                            <button type="button" class="dropdown-item pointer">ویرایش گروه</button>
                                        </a>
                                        <form method="post" action="{{ route('attributeGroup.destroy',[$group->id]) }}">
                                            {{ method_field('DELETE') }} {{ csrf_field() }}
                                            <input type="hidden" name="wizard" value="1">
                                            <button type="submit" class="dropdown-item pointer showConfirmDelete">حذف گروه</button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>

                        @foreach($group->attributes as $attribute)
                            <tr>
                                <td class="ps-4">
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
                                    <div class="dropdown">
                                        <a href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                           class="btn btn-dark btn-floating btn-icon btn-sm">
                                            <i aria-hidden="true" class="fa fa-ellipsis-v"></i>
                                        </a>
                                        <div class="dropdown-menu">
                                            <a href="{{ route('createAttributeItem',[$attribute->id,'wizard'=>1,'wizard_category'=>$category->id]) }}">
                                                <button type="button" class="dropdown-item pointer">افزودن مقدار</button>
                                            </a>
                                            <a href="{{ route('attributeItem.show',[$attribute->id,'wizard'=>1]) }}">
                                                <button type="button" class="dropdown-item pointer">مدیریت مقادیر</button>
                                            </a>
                                            {{-- مثل لیست ویژگی‌های پنل، ویرایش فقط برای select و color --}}
                                            @if($attribute->type == 'select' || $attribute->type == 'color')
                                                <a href="{{ route('attribute.edit',[$attribute->id,'wizard'=>1]) }}">
                                                    <button type="button" class="dropdown-item pointer">ویرایش ویژگی</button>
                                                </a>
                                            @endif
                                            <form method="post" action="{{ route('attribute.destroy',[$attribute->id]) }}">
                                                {{ method_field('DELETE') }} {{ csrf_field() }}
                                                <input type="hidden" name="wizard" value="1">
                                                <button type="submit" class="dropdown-item pointer showConfirmDelete">حذف ویژگی</button>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
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

@section('css')
    <link rel="stylesheet" href="{{asset('assets/dashboard/css/site/sweetalert2.css')}}">
@stop
@section('js')
    <script src="{{ asset('assets/dashboard/js/site/sweetalert2.all.js') }}"></script>
@stop
