@extends('site.master')

@section('title')
تکمیل سفارش | فاکتور: {{ $factor->resnumber }}
@endsection
@section('content')

@guest()
@include('auth.layout.check_register')
@include('auth.layout.login')
@endguest
@auth()
<div class="container">
    <div class="row">
        @include('site.layouts.errors')
    </div>
</div>
<div class="login">
    <div class="login-section bg-gray py-5">
        <div class="container">
            <div class="row">
                <div class="col-12 d-flex justify-content-between align-items-center flex-column flex-md-row">
                    @if(session()->get('payment') == 'success')
                    <div class="d-flex justify-content-center align-items-center mb-2 mb-md-0">
                        <i class="far fa-check-circle text-green font-16 ml-2"></i>
                        <span class="font-18 bold text-green">پرداخت با موفقیت انجام شد</span>
                    </div>
                    @endif
                </div>
                <div class="col-12 mt-2">
                    @foreach($items as $key => $item)
                    @if ($item['is_changeable'])
                    <div class="{{ $item['is_changeable'] ? '' : 'd-none' }}" id="first-data">
                        <form action="{{ route('checkout.store')  }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="items[{{ $key }}][id]" value="{{ $item['id'] }}">
                            @if(!is_null($item['centers']) || !is_null($item['forms']))
                            <div class="bg-white shadow rounded mb-4">
                                <div class="bg-light p-3 font-14 d-flex align-items-center justify-content-between">
                                    <div>
                                        @if(!is_null($item['service_item']))
                                        <span>{{ $item['service']->title . '-' . $item['service_item']->title
                                            }}</span>
                                        @else
                                        <span>{{ $item['service']->title }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="row p-3 position-relative">
                                    @if($item['service']->center && count($item['centers']) > 0)
                                    <div class="col-12 col-md-6 mb-2">
                                        <label for="items[{{ $key }}][center]">انتخاب مرکز:</label>
                                        <select name="items[{{ $key }}][center]" id="" class="form-control" required>
                                            <option value="">انتخاب مرکز</option>
                                            @foreach($item['centers'] as $center)
                                            <option {{$item['center_id']==$center->id?'selected' : ''}} value="{{
                                                $center->id }}"
                                                class="font-12">{{ $center->title. ' / ' . $center->address}}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    {{-- <div class="col-12 mb-3">
                                        <label>نوع دریافت خدمت :</label>
                                        <br>
                                        نقد <input type="radio" name="items[{{ $key }}][type]" value="0" {{
                                            !$item['type']?'checked':'' }} id="cash">
                                        اقساط<input type="radio" name="items[{{ $key }}][type]" value="1" {{
                                            $item['type']?'checked':'' }} id="installment">
                                    </div> --}}
                                    {{-- <div class="col-12 mb-3" id="guarantee_type">
                                        <label>
                                            نوع ضمانت
                                            (فقط مختص پرونده اقساطی)
                                        </label>
                                        <br>
                                        چک <input type="radio" {{$item['guarantee_type']=='check' ?'checked':''}}
                                            name="items[{{ $key }}][guarantee_type]" value="check">
                                        سفته <input type="radio" {{$item['guarantee_type']=='promissorynote'
                                            ?'checked':''}} name="items[{{ $key }}][guarantee_type]"
                                            value="promissorynote">
                                    </div> --}}
                                    @endif
                                    @if(!is_null($item['forms']))
                                    @foreach($item['forms'] as $form)
                                    @if (in_array('site' , $form->data_display_level))
                                    @switch($form->type)
                                    @case('number')
                                    <div class="col-12 col-md-6 mb-2">
                                        {!! $form->help !!}
                                        <input type="number" class="form-control {{ $form->class }}" {{ $form->required
                                        == true ? 'required' : '' }} placeholder="{{
                                        $form->placeholder }}"
                                        value="{{ $form->value }}"
                                        name="items[{{ $key }}][{{ $form->name }}]">
                                    </div>
                                    @break
                                    @case('text')
                                    <div class="col-12 col-md-6 mb-2">
                                        {!! $form->help !!}
                                        {{-- <label for="items[{{ $key }}][{{ $form->name }}]">{{ $form->help
                                            }}</label>--}}
                                        <input type="text" class="form-control {{ $form->class }}" {{ $form->required ==
                                        true ? 'required' : '' }} placeholder="{{
                                        $form->placeholder }}"
                                        value="{{ $form->value }}"
                                        name="items[{{ $key }}][{{ $form->name }}]">
                                    </div>
                                    @break
                                    @case('textarea')
                                    <div class="col-12 mb-2">
                                        {!! $form->help !!}
                                        <textarea class="form-control {{ $form->class }}" {{
                                            $form->required == true ? 'required' : '' }} placeholder="{{
                                            $form->placeholder }}"
                                            name="items[{{ $key }}][{{ $form->name }}]" row="6">{{ $form->value }}</textarea>
                                    </div>
                                    @break
                                    @case('select')
                                    <div class="col-12 col-md-6 mb-2">
                                        {!! $form->help !!}
                                        <select class="form-control {{ $form->class }}" {{ $form->required == true ?
                                            'required' : '' }} name="items[{{ $key }}][{{ $form->name }}]">
                                            <option value="">انتخاب کنید</option>
                                            @foreach($form->beforeformitems as $select_item)
                                            <option value="{{ $select_item->value }}">{{ $select_item->title }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @break
                                    @case('checkbox')
                                    <div class="col-12 col-md-12 mb-3">
                                        {!! $form->help !!}
                                        <br>
                                        @foreach($form->beforeformitems as $beforeformitem)
                                        {{ $beforeformitem->title }}
                                        @if(isset($item['factor_values'][$form->name]))
                                        <input type="{{ $form->type }}" name="items[{{ $key }}][{{$form->name }}][]"
                                            value="{{ $beforeformitem->value }}" {{ in_array($beforeformitem->value
                                        , $item['factor_values'][$form->name]) ? 'checked disabled' : '' }}>
                                        @else
                                        <input type="{{ $form->type }}" name="items[{{ $key }}][{{$form->name }}][]"
                                            value="{{ $beforeformitem->value }}">
                                        @endif
                                        @endforeach
                                    </div>
                                    @break
                                    @case('radio')
                                    <div class="col-12 col-md-12 mb-3">
                                        {!! $form->help !!}
                                        <br>
                                        @foreach($form->beforeformitems as $beforeformitem)
                                        {{ $beforeformitem->title }}
                                        @if(isset($item['factor_values'][$form->name]))
                                        <input type="{{ $form->type }}" name="item[{{ $form->name }}]"
                                            value="{{ $beforeformitem->value }}" {{ $beforeformitem->value ==
                                        $factorItem['factor_values'][$form->name] ? 'checked' : '' }} required>
                                        @else
                                        <input type="{{ $form->type }}" name="item[{{ $form->name }}]"
                                            value="{{ $beforeformitem->value }}" required>
                                        @endif
                                        @endforeach
                                    </div>
                                    @break
                                    @case('excel')
                                    <div class="col-12 mb-2">
                                        <a href="{{ asset($form->file) }}" download>
                                            <img src="https://images.freeimages.com/fic/images/icons/2795/office_2013_hd/2000/excel.png"
                                                class="img-fluid" width="100">
                                        </a>
                                    </div>
                                    <label>{{ $form->placeholder }}</label>
                                    @break
                                    @case('image')
                                    <div class="col-12 mb-2">
                                        <a href="{{ asset($form->file) }}" download>
                                            <img src="{{ asset($form->file) }}" class="img-fluid" width="300">
                                        </a>
                                    </div>
                                    <label class="text-center">{{ $form->placeholder }}</label>
                                    @break
                                    @case('multi-select')
                                    <div class="col-12 col-md-4 mb-2">
                                        {!! $form->help !!}
                                        <select multiple class="form-control selectpicker bg-secondary" {{ $form->required == true ?
                                            'required' : '' }} name="items[{{ $key }}][{{
                                            $form->name }}][]">
                                            <option value="" selected disabled>انتخاب کنید</option>
                                            @foreach($form->beforeformitems as $select_item)
                                            <option value="{{ $select_item->value }}" class="text-right">{{
                                                $select_item->title }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @break
                                    @case('file')
                                    <div class="col-12 col-md-4 mb-2 mt-3">
                                        <div class="card">
                                            @if(!isset($item['factor_values'][$form->name]))
                                            <p class="my-4 text-danger text-center">
                                                ثبت نشده</p>
                                            <div class="card-body">
                                                {!! $form->help !!}
                                                <input name="items[{{ $key }}][{{ $form->name }}]" type="file"
                                                    data-buttonname="btn-white" accept=".jpg,.jpeg,.png">
                                            </div>
                                            @else
                                            <a href="{{ asset($item['factor_values'][$form->name]) }}">
                                                <img src="{{ asset($item['factor_values'][$form->name]) }}"
                                                    class="card-img-top" height="150px">
                                            </a>
                                            <div class="card-body">
                                                {!! $form->help !!}
                                                <input name="items[{{ $key }}][{{ $form->name }}]" type="file"
                                                    data-buttonname="btn-white" accept=".jpg,.jpeg,.png">
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                    @break
                                    @case('multi_file')
                                    <div class="col-12">
                                        {!! $form->help !!}
                                    </div>
                                    <div class="col-12">
                                        <div id="addnew" class='file file--upload'>
                                            <label for='{{ $form->name }}'>
                                                <img class="upload-file" src="{{asset('img/upload.jpg')}}"
                                                    class="img-fluid">
                                                <h6>بارگذاری تصاویر</h6>
                                            </label>
                                            <input name="items[{{ $key }}][{{ $form->name }}][]" type="file"
                                                id="{{ $form->name }}"
                                                class="filestyle file-upload file_upload_ajax file-up"
                                                data-buttonname="btn-white">
                                        </div>
                                        <div id="{{ $form->name }}_newlink" class="col-12 row">
                                            <div>
                                            </div>
                                        </div>
                                        <div id="{{ $form->name }}_newlinktpl">
                                            <div id="{{ $form->name }}_src" class="show"></div>
                                            <input type="hidden" id="{{ $form->name }}" class="path"
                                                name="items[{{ $key }}][{{ 'url'.$form->name }}][]">
                                        </div>
                                    </div>
                                    <div class="col-12 row">
                                        @if(isset($item['factor_values'][$form->name]))
                                        @foreach($item['factor_values'][$form->name] as $image)
                                        <div class="card col-md-3">
                                            <a href="{{ asset($image) }}" target="_blank">
                                                <img src="{{ $image }}" class="card-img-top" height="150px">
                                            </a>
                                        </div>
                                        @endforeach
                                        @endif
                                    </div>
                                    @break
                                    @endswitch
                                    @endif
                                    @endforeach
                                    @endif
                                </div>
                            </div>
                            @else
                            <div class="bg-white shadow rounded mb-4">
                                <div class="bg-light p-3 font-14 d-flex align-items-center justify-content-between">
                                    <div>
                                        @if(!is_null($item['service_item']))
                                        <span>{{ $item['service']->title . '-' . $item['service_item']->title
                                            }}</span>
                                        @else
                                        <span>{{ $item['service']->title }}</span>
                                        @endif

                                    </div>
                                    <i class="fa fa-check font-18 text-success"></i>
                                </div>
                            </div>
                            @endif
                            <input type="submit" class="btn btn-success px-4" value="ثبت ">
                        </form>
                        @if($item['service']->isContract && $item['type'])
                        <div class="col-12  ">
                            <hr>
                            <span id="next" class="btn btn-outline-primary" style="cursor: pointer">
                                << مرحله بعد </span>
                                    <hr>
                        </div>
                        @endif
                    </div>
                    @else
                    <div class="text-center alert alert-success">
                        با تشکر، سفارش شما تکمیل شد و در دست بررسی قرار دارد.
                        <br>
                        <a href="/factorItem/{{ $item['resnumber'] }}"><button type="button"
                                class="btn btn-success mt-2">برای مشاهده پرونده اینجا کلیک کنید</button></a>
                    </div>
                    @endif
                    @if($item['service']->isContract && $item['type'])
                    <div id="file-content" class="mt-4 mt-md-0">
                        <div class="tab-content" id="nav-tabContent">
                            <form action="{{ route('checkout.upload') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="item[id]" value="{{ $item['id'] }}">
                                <input type="hidden" name="user_id" value="{{ $factor->user->id }}">
                                <div class="tab-pane fade show active" id="list-profile" role="tabpanel"
                                    aria-labelledby="list-profile-list">
                                    @foreach($errors->all() as $error)
                                    <div class="alert alert-danger mb-4" role="alert">
                                        {{ $error }}
                                    </div>
                                    @endforeach
                                    <div class="col-12 card profile-box bg-gray py-3 my-4 position-relative">
                                        <div class="alert alert-dark border-dark ">
                                            <i class="fa fa-info-circle "></i>
                                            لطفا تصاویر مربوط به مدارک ذکر شده را از طریق فرم زیر ارسال
                                            نمایید .
                                        </div>
                                        @include('layout.uploadSample')

                                        <div class="col-12">
                                            <div class="card-group">
                                                <div class="col-12 mb-2 mt-4">
                                                    <h4>اطلاعات وام گیرنده</h4>
                                                    <hr>
                                                </div>
                                                <div class="col-12 col-md-4">
                                                    <div class="card">
                                                        @if($factor->user->imgmeli)
                                                        <div id="imgmeli_src" class="show">
                                                            <a href="{{ asset($factor->user->imgmeli) }}"
                                                                target="_blank">
                                                                <img src="{{ asset($factor->user->imgmeli) }}"
                                                                    class="card-img-top" height="150px">
                                                            </a>
                                                        </div>
                                                        @else
                                                        <div id="imgmeli_src" class="show">
                                                            <img class="card-img-top" height="150px">
                                                        </div>
                                                        @endif
                                                        <div class="card-body">
                                                            <label>تصویر کارت ملی</label>
                                                            <br>
                                                            <label for="imgmeli" id="up-label"
                                                                class="btn badge-secondary"><i class="fa fa-upload ml-2"
                                                                    aria-hidden="true"></i> انتخاب تصویر </label>
                                                            <input name="imgmeli" type="file" id="imgmeli"
                                                                class="filestyle file-upload file_upload_ajax file-up"
                                                                data-buttonname="btn-white" accept=".jpg,.jpeg,.png">
                                                            <input type="hidden" id="imgmeli" class="path"
                                                                name="urlimgmeli">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-12 col-md-4">
                                                    <div class="card">
                                                        @if($factor->user->imgshenasname)
                                                        <div id="imgshenasname_src" class="show">
                                                            <a href="{{ asset($factor->user->imgshenasname) }}"
                                                                target="_blank">
                                                                <img src="{{ asset($factor->user->imgshenasname) }}"
                                                                    class="card-img-top" height="150px">
                                                            </a>
                                                        </div>
                                                        @else
                                                        <div id="imgshenasname_src" class="show">
                                                            <img class="card-img-top" height="150px">
                                                        </div>
                                                        @endif
                                                        <div class="card-body">
                                                            <label>تصویر شناسنامه</label>
                                                            <br>
                                                            <label for="imgshenasname" id="up-label"
                                                                class="btn badge-secondary"><i class="fa fa-upload ml-2"
                                                                    aria-hidden="true"></i> انتخاب تصویر </label>
                                                            <input name="imgshenasname" type="file" id="imgshenasname"
                                                                class="filestyle file-upload file_upload_ajax file-up"
                                                                data-buttonname="btn-white" accept=".jpg,.jpeg,.png">
                                                            <input type="hidden" id="imgshenasname" class="path"
                                                                name="urlimgshenasname">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-12 col-md-4">
                                                    <div class="card">
                                                        @if($factor->user->imgturnover)
                                                        <div id="imgturnover_src" class="show">
                                                            <a href="{{ asset($factor->user->imgturnover) }}"
                                                                target="_blank">
                                                                <img src="{{ asset($factor->user->imgturnover) }}"
                                                                    class="card-img-top" height="150px">
                                                            </a>
                                                        </div>
                                                        @else
                                                        <div id="imgturnover_src" class="show">
                                                            <img class="card-img-top" height="150px">
                                                        </div>
                                                        @endif
                                                        <div class="card-body">
                                                            <label>تصویر ثنا</label>
                                                            <br>
                                                            <label for="imgturnover" id="up-label"
                                                                class="btn badge-secondary"><i class="fa fa-upload ml-2"
                                                                    aria-hidden="true"></i> انتخاب تصویر </label>
                                                            <input name="imgturnover" type="file" id="imgturnover"
                                                                class="filestyle file-upload file_upload_ajax file-up"
                                                                data-buttonname="btn-white" accept=".jpg,.jpeg,.png">
                                                            <input type="hidden" id="imgturnover" class="path"
                                                                name="urlimgturnover">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-12 mb-2 mt-4">
                                                    @if($item['guarantee_type'] != 'promissorynote')
                                                    <h4>اطلاعات ضامن</h4>
                                                    @else
                                                    <h4>اطلاعات ضامن</h4>
                                                    <h4 class="alert alert-dark">(منظور از ضامن یک نفر از اعضاء درجه
                                                        یک خانواده به عنوان معرف کافیست)</h4>
                                                    @endif
                                                    <hr>
                                                </div>
                                                @foreach($item['contract_forms'] as $contract_form)
                                                @if($contract_form->value == $item['guarantee_type'])
                                                @switch($contract_form->type)
                                                @case('file')
                                                <div class="col-12 col-md-4 mb-2 mt-3">
                                                    <div class="card">
                                                        @if(!isset($item['contract_values'][$contract_form->name]))
                                                        <p class="my-4 text-danger text-center">
                                                            ثبت نشده</p>
                                                        <div class="card-body">
                                                            {!! $contract_form->help !!}

                                                            <input name="item[{{ $contract_form->name }}]" type="file"
                                                                data-buttonname="btn-white" accept=".jpg,.jpeg,.png">
                                                        </div>
                                                        @else
                                                        <a
                                                            href="{{ asset($item['contract_values'][$contract_form->name]) }}">
                                                            <img src="{{ asset($item['contract_values'][$contract_form->name]) }}"
                                                                class="card-img-top" height="150px">
                                                        </a>
                                                        <div class="card-body">
                                                            {!! $contract_form->help !!}
                                                            <input name="item[{{ $contract_form->name }}]" type="file"
                                                                data-buttonname="btn-white" accept=".jpg,.jpeg,.png">
                                                        </div>
                                                        @endif
                                                    </div>
                                                </div>
                                                @break
                                                @case('text')
                                                <div class="col-12 col-md-3 mb-2">

                                                    {!! $contract_form->help !!}
                                                    {{-- <label for="item[{{ $contract_form->name }}]">--}}

                                                        {{-- {{--}}
                                                        {{-- $contract_form->help }}</label>--}}
                                                    <input type="text" class="form-control {{ $contract_form->class }}"
                                                        {{ $contract_form->required == true ? 'required' : '' }}
                                                    placeholder="{{ $contract_form->placeholder }}"
                                                    value="{{ isset($item['contract_values'][$contract_form->name])
                                                    ? $item['contract_values'][$contract_form->name] : '' }}"
                                                    name="item[{{ $contract_form->name }}]">
                                                </div>
                                                @break
                                                @case('select')
                                                <div class="col-12 col-md-6 mb-2">
                                                    {!! $contract_form->help !!}
                                                    <select class="form-control {{ $contract_form->class }}" {{
                                                        $contract_form->required == true ? 'required' : '' }}
                                                        name="item[{{ $contract_form->name }}]">
                                                        <option value="">انتخاب کنید
                                                        </option>
                                                        @foreach($contract_form->beforeformitems as $select_item)
                                                        <option value="{{ $select_item->value }}" {{
                                                            isset($item['contract_values'][$contract_form->name]) &&
                                                            $select_item->value ==
                                                            $item['contract_values'][$contract_form->name] ?
                                                            'selected' : '' }}>{{ $select_item->title }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                @break
                                                @endswitch
                                                @endif
                                                @endforeach
                                            </div>
                                        </div>
                                        <p class="bold font-16 mt-3 text-black-50">راهنمای بارگزاری
                                            فایل در سامانه</p>
                                        <ul class="px-0 mr-3">
                                            <li class="my-1">پسوند های مجاز: jpg,jpeg,png</li>
                                            <li class="my-1">حجم فایل نباید بیشتر از 6 مگابایت
                                                باشد
                                            </li>
                                        </ul>
                                        {{-- if service category is health-service--}}
                                        @if(false)
                                        @foreach($factor->factorItems as $factorItem)
                                        @if(isset($factorItem->factoritemable) &&
                                        isset($factorItem->factoritemable->category) &&
                                        $factorItem->factoritemable->category->id == 3)

                                        <div class="card">
                                            <div class="card-header">
                                                اطلاعات پزشکی :
                                            </div>
                                            <div class="card-body">
                                                @foreach($diseases as $disease)
                                                <div class="form-check-inline">
                                                    <label style="cursor: pointer" class="form-check-label">
                                                        <input name="diseases[]" type="checkbox"
                                                            class="form-check-input" value="{{$disease->id}}">
                                                        {{$disease->name}}
                                                    </label>
                                                </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        @break
                                        @endif
                                        @endforeach
                                        @endif
                                        <div class="col-12 mb-2 mt-auto pt-2">
                                            <input type="submit" class="btn btn-success px-4 mb-3" value="ثبت">
                                        </div>
                                        @if($item['is_changeable'])
                                        <div class="col-12 mb-2 mt-auto">
                                            <hr>
                                            <span id="previous" class="btn btn-outline-primary "
                                                style="cursor: pointer"> مرحله قبل >></span>
                                            <hr>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endauth
@endsection
@section('js')
<script src="/assets/dashboard/js/site/adminJs.js"></script>
<script src="{{ asset('/assets/dashboard/js/layouts.js') }}"></script>
<script src="/assets/site/js/fix-number.js?v=1"></script>
<script src="/assets/site/js/get-service.js?v=1"></script>
<script src="/assets/site/js/checkout.js"></script>
<script>
    @if(session()->get('successByMessage'))
    toastr.success('{{session()->get('successByMessage')}}');
    @elseif(session()->get('failByMessage'))
    toastr.error('{{session()->get('failByMessage')}}');
    @endif
</script>
@endsection
