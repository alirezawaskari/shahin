@extends('Admin.master')
@section('content')
    @php
        $in_wizard = (bool) request('wizard');
        $wizard_query = $in_wizard ? ['wizard' => 1] : [];
    @endphp
    <div class="page-header">
        <div>
            <h3>لیست دسته بندی</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                    <li class="breadcrumb-item active" aria-current="page">لیست دسته بندی
                        {{\App\Models\Category::findPath($selected_cat->id)}}
                        / {{$selected_cat->title}}</li>
                </ol>
            </nav>
        </div>
        <div role="group" class="btn-group">
            <button id="btnGroupDrop1" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                    class="btn btn-primary dropdown-toggle">اقدامات
            </button>
            <div aria-labelledby="btnGroupDrop1" x-placement="bottom-start" class="dropdown-menu dropdown-menu-list">
                <a href="{{route('category.show',[$selected_cat->id] + $wizard_query)}}" class="dropdown-item"> افزودن در دسته
                    بندی {{$selected_cat->title}}  </a>
                @if($selected_cat->depth >= 1)
                    <a href="{{route('category.subCategory',[$selected_cat->parent_id] + $wizard_query)}}" class="dropdown-item"> بازگشت </a>
                @endif
            </div>
        </div>

    </div>

    {{-- وقتی از ویزارد ایجاد محصول وارد این صفحه شده‌ایم، مرحله جاری را نشان می‌دهیم
         و پرچم wizard را روی لینک‌های ادامه مسیر نگه می‌داریم. --}}
    @if($in_wizard)
        @include('Admin.productWizard.partials.steps',[
            'current' => 'category',
            'kind'    => \App\Http\Controllers\Admin\ProductWizardController::kindOfCategory($selected_cat),
        ])
        <div class="alert alert-info">
            یک دسته‌بندی جدید بسازید یا وارد زیرمجموعه دسته‌بندی موجود شوید؛ پس از ایجاد، به مرحله ثبت
            کالا/خدمت هدایت می‌شوید.
            <a href="{{route('category.show',[$selected_cat->id] + $wizard_query)}}" class="btn btn-sm btn-success ms-2">
                ایجاد دسته‌بندی در «{{$selected_cat->title}}»
            </a>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form method="get" action="{{route('category.subCategory',$selected_cat->id)}}">
                @if($in_wizard)<input type="hidden" name="wizard" value="1">@endif
                <div class="row">
                    <div class="col-12 col-md-3 mb-2">
                        <input class="form-control" name="id" placeholder="کد دسته بندی"
                               value="@if(isset($queries['id'])) @if($queries['id']) {{$queries['id']}} @endif @endif">
                    </div>

                    <div class="col-12 col-md-3 mb-2">
                        <input class="form-control" name="title" placeholder=" عنوان فارسی دسته بندی"
                               value="@if(isset($queries['title'])) @if($queries['title']) {{$queries['title']}} @endif @endif">
                    </div>

                    <div class="col-12 col-md-3 mb-2">
                        <button type="submit" id="filter" class="btn btn-outline-primary py-2 px-4">فیلتر</button>
                        <a type="button" href="{{route('category.subCategory',[$selected_cat->id] + $wizard_query)}}" class="btn btn-outline-danger py-2 px-4">حذف فیلتر ها</a>
                    </div>

                </div>
            </form>
        </div>
    </div>


    <div class="card position-relative">
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>شناسه</th>
                    <th>عنوان</th>
                    <th>عنوان انگلیسی</th>
                    <th>نمایش در سایت</th>
                    <th>قابل سفارش</th>
                    <th>اقدام</th>
                </tr>
                </thead>
                <tbody>
                @foreach($categories as $item)
                    <tr>
                        <td>{{$item->id}} </td>
                        <td>{{$item->title}} </td>
                        <td>{{$item->en_title}} </td>
                        <td>{!! \App\Helpers\Helper::iconIsItOrNot($item->isActive) !!} </td>
                        <td>{!! \App\Helpers\Helper::iconIsItOrNot($item->displayOrder) !!} </td>
                        <td>
                            <div data-v-15965e3b="" class="dropdown disInline">
                                <a href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                   class="btn btn-dark btn-floating btn-icon btn-sm">
                                    <i aria-hidden="true" class="fa fa-ellipsis-v"></i>
                                </a>
                                <div class="dropdown-menu">
                                    @if($in_wizard)
                                        {{-- ادامه ویزارد با یک دسته‌بندی موجود، بدون اجبار به ساخت دسته‌بندی جدید --}}
                                        @php
                                            $item_kind = \App\Http\Controllers\Admin\ProductWizardController::kindOfCategory($item);
                                        @endphp
                                        <a href="{{\App\Http\Controllers\Admin\ProductWizardController::createUrl($item_kind,$item->id)}}">
                                            <button type="button" class="dropdown-item pointer">
                                                ثبت {{$item_kind === 'service' ? 'خدمت' : 'کالا'}} در این دسته‌بندی
                                            </button>
                                        </a>
                                    @endif
                                    <a href="{{route('category.edit',[$item->id])}}">
                                        <button type="button" class="dropdown-item pointer">ویرایش</button>
                                    </a>

                                    @if($item->parent_id != 2)
                                    <a href="{{route('category.subCategory',[$item->id] + $wizard_query)}}" >
                                        <button type="button" class="dropdown-item pointer">زیر مجموعه
                                            <span class="lineHZ">({{\App\Models\Category::countChild($item->id)}}
                                                )</span></button>
                                    </a>

                                    @if(\App\Models\Category::hasChild($item->id) == 0)
                                        <a href="{{route('attributeGroup.index',['category_id'=>$item->id])}}">
                                            <button type="button" class="dropdown-item pointer"> لیست ویژگی</button>
                                        </a>
                                        <a href="{{route('brand.show',[$item->id])}}">
                                            <button type="button" class="dropdown-item pointer"> لیست برند</button>
                                        </a>
                                    @endif
                                        @if($item->depth == 1)
                                            <a href="{{route('installment.show',[$item->id])}}">
                                                <button type="button" class="dropdown-item pointer">اقساط</button>
                                            </a>
                                        @endif
                                    @endif

                                    <form method="post" action="{{route('category.destroy',[$item->id])}}">
                                        {{ method_field('DELETE') }}
                                        {{csrf_field()}}
                                        <button type="submit" class="dropdown-item pointer showConfirmDelete">حذف</button>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            {{$categories->links()}}
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="{{asset('assets/dashboard/css/site/sweetalert2.css')}}">
@stop
@section('js')
    <script src="{{ asset('assets/dashboard/js/site/sweetalert2.all.js') }}"></script>
@stop
