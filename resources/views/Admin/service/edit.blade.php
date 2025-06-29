@extends('Admin.master')
@section('title'){{ $service->title }}@endsection

@section('content')
    <div class="card">
        <div class="card-body ">
            <div>
                @include('Admin.layouts.errors')
            </div>
            <div class="transfer_message_box"></div>
        </div>
        <div class="card-body">
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item  flex-fill text-center nav-item-service">
                    <a class="nav-link active " id="firstData-tab" data-toggle="tab" href="#home" role="tab"
                       aria-controls="home" aria-selected="true"><i class="fa fa-list"></i> اطلاعات اولیه</a>
                </li>
                <li class="nav-item  flex-fill text-center nav-item-service">
                    <a class="nav-link  " id="firstData-tab" data-toggle="tab" href="#rules" role="tab"
                       aria-controls="rules" aria-selected="true"><i class="fa fa-file-text-o"></i> قوانین</a>
                </li>
                <li class="nav-item  flex-fill text-center nav-item-service">
                    <a class="nav-link" id="text-tab" data-toggle="tab" href="#profile" role="tab"
                       aria-controls="profile" aria-selected="false"><i class="fa fa-text-height"></i> متن</a>
                </li>
                <li class="nav-item  flex-fill text-center nav-item-service">
                    <a class="nav-link  " id="firstImages-tab" data-toggle="tab" href="#contact" role="tab"
                       aria-controls="contact" aria-selected="false"> <i class="fa fa-image"></i> تصاویر اولیه</a>
                </li>
                <li class="nav-item  flex-fill text-center nav-item-service">
                    <a class="nav-link" id="secondData-tab" data-toggle="tab" href="#second-data" role="tab"
                       aria-controls="second-data" aria-selected="false"><i class="fa fa-info-circle"></i>اطلاعات (سایت
                        جدید)</a>
                </li>
                <li class="nav-item  flex-fill text-center nav-item-service">
                    <a class="nav-link" id="questionData-tab" data-toggle="tab" href="#question-data" role="tab"
                       aria-controls="question-data" aria-selected="false"><i class="fa fa-question-circle"></i>سوالات
                        متداول </a>
                </li>
                <li class="nav-item  flex-fill text-center nav-item-service">
                    <a class="nav-link" id="portfolio-tab" data-toggle="tab" href="#portfolio-data" role="tab"
                       aria-controls="portfolio-data" aria-selected="false"> <i class="fa fa-list-alt"></i>نمونه
                        کارها</a>
                </li>
                <li class="nav-item  flex-fill text-center nav-item-service">
                    <a class="nav-link" id="siteImages-tab" data-toggle="tab" href="#siteImages-data" role="tab"
                       aria-controls="siteImages-data" aria-selected="false"><i class="fa fa-file-image-o"></i>
                        تصاویر سایت
                    </a>
                </li>
                <li class="nav-item  flex-fill text-center nav-item-service">
                    <a class="nav-link" id="session-titles-tab" data-toggle="tab" href="#sessionTitles-data" role="tab"
                       aria-controls="sessionTitles-data" aria-selected="false"><i class="fa fa-cogs"
                                                                                   aria-hidden="true"></i>
                        عنوان جدول مراجعات
                    </a>
                </li>
            </ul>
            <div class="tab-content" id="myTabContent">

                <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="firstData-tab">
                    <div class="container m-top-40">
                        <form action="{{route('service.update' , [$service->slug ])}}"
                              id="basic-information-form">
                            <input type="hidden" name="update_type" value="basic_information">
                            <div class="row">
                                <div class="col-12 col-md-3 mb-3">
                                    <label>عنوان</label>
                                    <input type="text" value="{{ $service->title }}" class="form-control" name="title"
                                           required>
                                </div>

                                <div class="col-12 col-md-6 mb-3">
                                    <label>عنوان لاتین (توجه داشته باشید که این عنوان در url نمایش داده میشود و حتما باید به صورت انگلیسی وارد شود.)</label>
                                    <input type="text" value="{{ $service->en_title }}" class="form-control"
                                           name="en_title" required>
                                </div>
                                <div class="col-12 col-md-3 mb-3">
                                    <label>عنوان فاکتور</label>
                                    <input type="text" value="{{ $service->factor_title }}" class="form-control"
                                           name="factor_title" required>
                                </div>
                                <div class="col-12 col-md-12 mb-3">
                                    <label>توضیحات</label>
                                    <input type="text" value="{{ $service->description }}" class="form-control"
                                           name="description"
                                           required>
                                </div>

                                <div class="col-12 col-md-1 mb-3">
                                    <label>وضعیت</label>

                                    <select class="form-control" name="status">
                                        <option value="1" {{ $service->status == 1 ? 'selected' : '' }}>
                                            فعال
                                        </option>

                                        <option value="0" {{ $service->status == 0 ? 'selected' : '' }}>
                                            غیر فعال
                                        </option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-2 mb-3">
                                    <label>نمایش در سایت مرکز تهران</label>
                                    <select class="form-control" name="isShow">
                                        <option value="1" {{ $service->isShow == true ? 'selected' : '' }}>
                                            فعال
                                        </option>
                                        <option value="0" {{ $service->isShow == false ? 'selected' : '' }}>
                                            غیر فعال
                                        </option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-2 mb-3">
                                    <label>دارای آیتم</label>

                                    <select class="form-control" name="items">
                                        <option value="0" {{ $service->items == false ? 'selected' : '' }}>
                                            خیر
                                        </option>
                                        <option value="1" {{ $service->items == true ? 'selected' : '' }}>
                                            بله
                                        </option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-1 mb-3">
                                    <label>دارای مرکز</label>

                                    <select class="form-control" name="center">
                                        <option value="0" {{ $service->center == false ? 'selected' : '' }}>
                                            خیر
                                        </option>
                                        <option value="1" {{ $service->center == true ? 'selected' : '' }}>
                                            بله
                                        </option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-2 mb-3">
                                    <label>قابلیت چند بار خرید</label>

                                    <select class="form-control" name="multiple">
                                        <option value="0" {{ $service->multiple == false ? 'selected' : '' }}>
                                            خیر
                                        </option>
                                        <option value="1" {{ $service->multiple == true ? 'selected' : '' }}>
                                            بله
                                        </option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-2 mb-3">
                                    <label>پیش نیاز ها</label>

                                    <select class="form-control select2" name="required[]" multiple>
                                        @foreach(\App\Models\Service::filter()->whereStatus(false)->where('id' , '!=' , $service->id)->get() as $ser)
                                            <option
                                                value="{{ $ser->id }}" {{ in_array(trim($ser->id) , $service->required ?? []) ?  'selected' : '' }}>{{ $ser->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 col-md-2 mb-3">
                                    <label>قابلیت دعوت دوستان</label>
                                    <select class="form-control" name="ability_invite_friend">
                                        <option
                                            value="0" {{ $service->ability_invite_friend == false ? 'selected' : '' }}>
                                            خیر
                                        </option>
                                        <option
                                            value="1" {{ $service->ability_invite_friend == true ? 'selected' : '' }}>
                                            بله
                                        </option>
                                    </select>
                                </div>

                                <div class="col-12 col-md-4 mb-3">
                                    <label>(مثال : 50)درصد پیش پرداخت</label>
                                    <input type="number" value="{{ number_format($service->percent) }}"
                                           class="form-control"
                                           name="percent" required>
                                </div>

                                <div class="col-12 col-md-4 mb-3">
                                    <label>درصد مالیات</label>
                                    <input type="number" value="{{ number_format($service->tax) }}"
                                           class="form-control"
                                           name="tax" required>
                                </div>

                                <div class="col-12 col-md-4 mb-3">
                                    <label>درصد سود سالیانه(مثال : 4)</label>
                                    <input type="number" value="{{ number_format($service->wage_percent) }}"
                                           class="form-control"
                                           name="wage_percent" required>
                                </div>

                                <div class="col-12 col-md-4 mb-3">
                                    <label>قیمت (ریال)</label>
                                    <input type="text" value="{{ number_format($service->price) }}" class="form-control"
                                           name="price"
                                           required oninput="separatePrices(this)">
                                </div>

                                <div class="col-12 col-md-6 mb-3">
                                    <label>خدمات رایگان همراه این خدمت</label>
                                    <select class="form-control select2" name="gift[]" multiple>
                                        @foreach(\App\Models\Service::filter()->whereStatus(false)->where('id' , '!=' , $service->id)->get() as $ser)
                                            <option value="{{ $ser->id }}" {{ in_array(trim($ser->id) , $service->gift ?? []) ? 'selected' :'' }}>{{ $ser->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 col-md-12 mb-3">
                                    <span class="btn btn-success" id="basic-information-submit">ثبت تغییرات</span>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <!--__________________________________________-->
                <div class="tab-pane fade  " id="rules" role="tabpanel" aria-labelledby="firstData-tab">
                    <div class="container m-top-40">
                        <form action="{{route('service.update' , [$service->slug ])}}"
                              id="rules-form">
                            <input type="hidden" name="update_type" value="rules">
                            <div class="row">

                                <div class="col-12 col-md-12 mb-3">
                                    <label>متن </label>
                                    <textarea class="form-control" name="service_rules">{!!$service->rules !!} </textarea>
                                </div>

                                <div class="col-12 col-md-12 mb-3">
                                    <span class="btn btn-success" id="rules-submit">ثبت تغییرات</span>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <!--__________________________________________-->
                <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="text-tab">
                    <div class="container m-top-40">
                        <form action="{{route('service.update' , [$service->slug ])}}"
                              id="text-information-form">
                            <input type="hidden" value="text_information" name="update_type">
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <div class="card">
                                        <div class="card-header">
                                            متن بخش اول (بالای فرم ثبت نام)
                                        </div>
                                        <div class="card-body">
                                            <textarea name="left_content">{{ $service->left_content }}</textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 mb-3">
                                    <div class="card">
                                        <div class="card-header">
                                            متن بخش دوم
                                        </div>
                                        <div class="card-body">
                                            <textarea name="txt_box_1">{{ $service->txt_box_1 }}</textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 mb-3">
                                    <div class="card">
                                        <div class="card-header">
                                            متن بخش سوم (بخش پایانی)
                                        </div>
                                        <div class="card-body">
                                            <textarea class="editor-content"
                                                      name="content">{!! $service->content !!} </textarea>
                                        </div>
                                    </div>
                                </div>


                            </div>
                            <div class="col-12 col-md-12 mb-3">
                                <span class="btn btn-success" id="text-information-submit">ثبت تغییرات</span>
                            </div>
                        </form>
                    </div>

                </div>
                <!--__________________________________________-->
                <div class="tab-pane fade" id="contact" role="tabpanel" aria-labelledby="firstImages-tab">
                    <div class="container m-top-40">
                        <form action="{{route('service.update' , [$service->slug ])}}" id="first-images-form"
                              method="post" enctype="multipart/form-data">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" value="first_images" name="update_type">
                            <div class="row">
                                <div class="col-12 col-md-3 mb-3 border border">
                                    <img class="img-thumbnail" src="{{asset($service->img_box_1)}}" alt="">
                                    <label>تصویر بخش اول</label>
                                    <label for="img_box_1" class="custom-file-upload">
                                        <i class="fa fa-cloud-upload"></i> بارگذاری تصویر
                                    </label>
                                    <input id="img_box_1" class="form-control btn btn-outline-info file-upload "
                                           type="file"
                                           name="img_box_1"/>
                                    <div id="img_box_1_src" class="show"></div>
                                </div>

                                <div class="col-12 col-md-3 mb-3 border">
                                    <img class="img-thumbnail" src="{{asset($service->thumb)}}" alt="">
                                    <label>تصویر اسلاید زیر فرم ثبت نام</label>
                                    <label for="thumb" class="custom-file-upload">
                                        <i class="fa fa-cloud-upload"></i> بارگذاری تصویر
                                    </label>
                                    <input id="thumb" class="form-control btn btn-outline-info file-upload " type="file"
                                           name="thumb"/>
                                    <div id="thumb_src" class="show"></div>

                                </div>
                                <div class="col-12 col-md-3 mb-3 border">
                                    <img class="img-thumbnail" src="{{asset($service->image)}}" alt="">
                                    <label>تصویر بخش دوم</label>
                                    <label for="image" class="custom-file-upload">
                                        <i class="fa fa-cloud-upload"></i> بارگذاری تصویر
                                    </label>
                                    <input id="image" class="form-control btn btn-outline-info file-upload" type="file"
                                           name="image"/>
                                    <div id="image_src" class="show"></div>

                                </div>
                                <div class="col-12 col-md-3 mb-3 border">
                                    <img class="img-thumbnail" src="{{asset($service->image_app)}}" alt="">
                                    <label>تصویر اصلی (استفاده شده درصفحه اول تک صفحه ای و فهرست خدمات)</label>
                                    <label for="image_app" class="custom-file-upload">
                                        <i class="fa fa-cloud-upload"></i> بارگذاری تصویر
                                    </label>
                                    <input id="image_app" class="form-control btn btn-outline-info file-upload "
                                           type="file"
                                           name="image_app"/>
                                    <div id="image_app_src" class="show"></div>
                                </div>
                                <div class="col-12 col-md-3 mb-3 border">
                                    <img class="img-thumbnail" src="{{asset($service->icon)}}" alt="">
                                    <label>(استفاده شده در صفحه اول سایت) تصویر آیکن</label>
                                    <label for="icon" class="custom-file-upload">
                                        <i class="fa fa-cloud-upload"></i> بارگذاری تصویر
                                    </label>
                                    <input id="icon" class="form-control btn btn-outline-info file-upload" type="file"
                                           name="icon"/>
                                    <div id="icon_src" class="show"></div>
                                </div>
                                {{-- <div class="col-12 col-md-3 mb-3 border">
                                    <img class="img-thumbnail" src="{{asset($service->files->where('title','desktop_slider')->first()->path??'')}}" alt="">
                                    <label>تصویر اسلایدر دسکتاپ</label>
                                    <label for="desktop_slider" class="custom-file-upload">
                                        <i class="fa fa-cloud-upload"></i> بارگذاری تصویر
                                    </label>
                                    <input id="desktop_slider" class="form-control btn btn-outline-info file-upload" type="file"
                                           name="desktop_slider"/>
                                    <div id="desktop_slider" class="show"></div>
                                </div> --}}
                                {{-- <div class="col-12 col-md-3 mb-3 border">
                                    <img class="img-thumbnail" src="{{asset($service->files->where('title','mobile_slider')->first()->path??'')}}" alt="">
                                    <label>تصویر اسلایدر موبایل</label>
                                    <label for="mobile_slider" class="custom-file-upload">
                                        <i class="fa fa-cloud-upload"></i> بارگذاری تصویر
                                    </label>
                                    <input id="mobile_slider" class="form-control btn btn-outline-info file-upload" type="file"
                                           name="mobile_slider"/>
                                    <div id="mobile_slider" class="show"></div>
                                </div> --}}
                                <div class="col-12 col-md-12 mb-3">
                                    <input class="btn btn-success" type="submit" value="ثبت تغییرات"
                                           id="first-images-submit">
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <!--__________________________________________-->
                <div class="tab-pane fade" id="second-data" role="tabpanel" aria-labelledby="secondData-tab">
                    <div class="container m-top-40">
                        <form action="{{route('service.update' , [$service->slug ])}}"
                              id="site-information-form">
                            <input type="hidden" value="site_information" name="update_type">
                            <div class="row">
                                <div class="col-12 col-md-4 mb-5">
                                    <label>دسته بندی *</label>
                                    <select class="form-control" name="category_id" title="">
                                        <option value="">لطفا انتخاب کنید</option>
                                        @foreach($service_categories as $service_category)
                                            <option value="{{$service_category->id}}"
                                                    @if($service_category->id == $service->category_id)
                                                    selected @endif>{{$service_category->title}}</option>
                                        @endforeach
                                    </select>
                                    @if($errors->has('category_id'))<span
                                        class="help-block sub-error">{{$errors->first('category_id')}}</span>@endif

                                </div>

                                <div class="col-12 col-md-4 mb-5">
                                    <label>کمترین اعتبار *</label>
                                    <input type="text" name="min_credit" id="currency-field"
                                           value="{{$service->min_credit}}"
                                           class="form-control" data-type="currency"
                                           placeholder="لطفا کمترین اعتبار را وارد کنید">
                                    @if($errors->has('min_credit'))
                                        <span class="help-block sub-error"> {{$errors->first('min_credit')}} </span>
                                    @endif
                                </div>
                                <div class="col-12 col-md-4 mb-5">
                                    <label>بیشترین اعتبار *</label>
                                    <input type="text" name="max_credit" id="currency-field"
                                           value="{{$service->max_credit}}"
                                           class="form-control" data-type="currency"
                                           placeholder="لطفا بیشترین اعتبار را وارد کنید">
                                    @if($errors->has('max_credit'))
                                        <span class="help-block sub-error"> {{$errors->first('max_credit')}} </span>
                                    @endif

                                </div>

                                <div class="col-12 col-md-3 mb-5">
                                    <label>دسته بندی های مشابه </label>
                                    <select name="similar_categories[]" title="" class="selectMulti form-control"
                                            multiple="multiple">
                                        @foreach($all_categories as $all_category)
                                            <option
                                                value="{{$all_category->id}}" {{ in_array(trim($all_category->id) , $similar_categories) ? 'selected' : '' }}
                                            >{{$all_category->title}}</option>
                                        @endforeach
                                    </select>
                                    @if($errors->has('similar_categories'))
                                        <span
                                            class="help-block sub-error"> {{$errors->first('similar_categories')}} </span>
                                    @endif

                                </div>
                                <div class="col-12 col-md-3 mb-5">
                                    <label>خدمات مشابه </label>
                                    <select name="similar_services[]" title="" class="selectMulti form-control"
                                            multiple="multiple">
                                        @foreach($all_services as $all_service)
                                            <option value="{{$all_service->id}}" {{ in_array(trim($all_service->id) , $similar_services) ?
                            'selected' : '' }}
                                            >{{$all_service->title}}</option>
                                        @endforeach
                                    </select>
                                    @if($errors->has('similar_services'))
                                        <span
                                            class="help-block sub-error"> {{$errors->first('similar_services')}} </span>
                                    @endif

                                </div>
                                <div class="col-12 col-md-3 mb-5">
                                    <label>نوع خرید *</label>
                                    <select name="purchase_type" class="form-control">
                                        <option value="0" @if($service->purchase_type == 0) selected @endif>آنلاین
                                        </option>
                                        <option value="1" @if($service->purchase_type == 1) selected @endif>حضوری
                                        </option>
                                        <option value="2" @if($service->purchase_type == 2) selected @endif>هر دو
                                        </option>
                                    </select>
                                    @if($errors->has('purchase_type'))
                                        <span class="help-block sub-error"> {{$errors->first('purchase_type')}} </span>
                                    @endif
                                </div>
                                <div class="col-12 col-md-3 mb-5">
                                    <label>نوع پرداخت *</label>
                                    <select name="pay_type" class="form-control">
                                        <option value="0" @if($service->pay_type == 0) selected @endif>نقدی</option>
                                        <option value="1" @if($service->pay_type == 1) selected @endif>اقساط</option>
                                        <option value="2" @if($service->pay_type == 2) selected @endif>هر دو</option>
                                    </select>
                                    @if($errors->has('pay_type'))
                                        <span class="help-block sub-error"> {{$errors->first('pay_type')}} </span>
                                    @endif
                                </div>

                                <div class="col-12 col-md-6 mb-5">
                                    <label>عنوان منو</label>

                                    <input id="menu_title_lbl" type="text" class="form-control" name="menu_title"
                                           value="{{$service->menu()->exists()?$service->menu->title:''}}"
                                           placeholder="لطفا عنوان منو را وارد کنید">

                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="is_menu" value="1"
                                                   @if($service->menu()->exists()) checked
                                                @endif>نمایش در منو
                                        </label>
                                        @if($errors->has('is_menu'))<span
                                            class="help-block sub-error">{{$errors->first('is_menu')}}</span>@endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-12 mb-3">
                                <span class="btn btn-success" id="site-information-submit">ثبت تغییرات</span>
                            </div>
                        </form>
                    </div>
                </div>
                <!--__________________________________________-->
                <div class="tab-pane fade" id="question-data" role="tabpanel" aria-labelledby="questionData-tab">
                    <div class="container m-top-40">
                        <form action="{{route('service.update' , [$service->slug ])}}"
                              id="question-insert-form">
                            <div class="border border-success rounded "
                                 style="margin-top: 20px;padding: 5px;padding-right: 10px">
                                <input type="hidden" value="insert_question" name="update_type">
                                <div class="col-12 col-md-12 text-right">
                                    <span class="badge badge-info  " style="margin-top: 20px"> ثبت آیتم جدید :</span>
                                </div>

                                <div class="form-row" style="margin-top: 20px">
                                    <div class="col-12 col-md-5 mb-3">
                                        <input class="form-control" type="text" placeholder="سوال را وارد نمایید !"
                                               name="question">
                                    </div>
                                    <div class="col-12 col-md-5 mb-3">
                                        <input class="form-control" type="text" placeholder="جواب را وارد نمایید !"
                                               name="answer">
                                    </div>
                                    <div class="col-12 col-md-2 mb-3">
                                        <span class="btn btn-success" id="insert-question-submit">ثبت </span>
                                    </div>
                                </div>
                            </div>
                            <hr>
                        </form>
                        <div class="row">
                            <div class="col-md-12 col-sm-12 col-xs-12 mb-5">
                                @include('Admin.partials.service.editAskedQuestion')
                            </div>
                        </div>
                    </div>
                </div>
                <!--__________________________________________-->
                <div class="tab-pane fade" id="portfolio-data" role="tabpanel" aria-labelledby="portfolio-tab">
                    <div class="container m-top-40">
                        <div class="row">
                            @if($service->workSamples()->count() > 0)
                                <div class="col-12 col-md-12 mb-5">
                                    <label> نمونه کار های ذخیره شده</label>
                                    @foreach($service->workSamples()->get() as $work_sample)
                                        <div class="div_remove_btn" id="work_sample_{{$work_sample->id}}">
                                            <img src="{{$work_sample->image}}" alt="{{$work_sample->title}}"
                                                 class="border-radius5"
                                                 width="80px">
                                            <a id="{{$work_sample->id}}" title="حذف عکس"
                                               class="btn btn-default remove_work_sample">
                                                <i class="fa fa-close" aria-hidden="true"></i></a>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                            <form action="{{route('service.update' , [$service->slug ])}}"
                                  id="portfolio-information-form">
                                <input type="hidden" name="update_type" value="portfolio_images">
                                <div class="col-md-12 col-sm-12 col-xs-12 mb-5">
                                    @include('Admin.partials.multipleImages',['has_link'=>0])
                                </div>
                                <div class="col-12 col-md-12 mb-3">
                                    <span class="btn btn-success" id="portfolio-information-submit">ثبت تغییرات</span>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <!--___________________________________________-->
                <div class="tab-pane fade" id="siteImages-data" role="tabpanel" aria-labelledby="siteImages-tab">
                    <div class="container m-top-40">
                        <form action="{{route('service.update' , [$service->slug ])}}" id="site-images-form">
                            <input type="hidden" name="update_type" value="site_images">
                            <div class="row">
                                @include('Admin.partials.editBannerHomePage',['model'=>$service])
                            </div>
                            <span class="btn btn-success" id="site-images-submit">ثبت تغییرات</span>
                        </form>
                    </div>
                </div>  <!--___________________________________________-->
                <div class="tab-pane fade" id="sessionTitles-data" role="tabpanel" aria-labelledby="sessionTitles-tab">
                    <div class="container m-top-40">
                        <div class="row">
                            <div class="form-row card border rounded col-md-6">
                                <div class="card-header">
                                    افزودن عنوان جدید
                                </div>
                                <div class="card-body">
                                    <div class="form-group col-md-8">
                                        <input type="text" class="form-control" id="new-session-title-input" required
                                               placeholder="عنوان را وارد نمایید...">

                                    </div>
                                    <div class="form-group col-md-6">
                                <span class="btn btn-info form-control"
                                      id="add-session-title-input">افزودن </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card border rounded">
                                    <div class="card-header">
                                        ویرایش عناوین
                                    </div>
                                    <div class="card-body">
                                        <form action="{{route('service.update' , [$service->slug ])}}"
                                              id="session-titles-form">
                                            <input type="hidden" name="update_type" value="session_titles">
                                            <div class="form-row">
                                                <div class="form-group col-md-8">
                                                    <ul id="sortable" class="list-group shadow-sm">
                                                        @if(is_array($service->session_attributes) && isset($service->session_attributes['session_titles']))
                                                            @foreach($service->session_attributes['session_titles'] as $item)
                                                                <li class="list-group-item" style="cursor: pointer"
                                                                    data-name="{{$item}}">
                                                                    {{$item}}
                                                                    <i class="fa fa-trash  text-danger trash-title float-right btn btn-sm btn-outline-warning"></i>
                                                                </li>
                                                            @endforeach
                                                        @endif
                                                    </ul>
                                                </div>
                                                <div class="form-group col-md-12">
                                                    <span class="btn btn-success"
                                                          id="session-titles-submit">ثبت تغییرات</span>
                                                </div>
                                            </div>

                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
@section('js')
    <script src="{{ asset('/assets/script/ckeditor/ckeditor.js') }}"></script>
    <script src="{{ asset('/assets/dashboard/js/layouts.js?v=2.0') }}"></script>
    <script src="{{ asset('/assets/dashboard/js/serviceUpdate.js?v=2.1') }}"></script>
    <script>
        @if(session()->get('message'))
        toastr.success('{{ session()->get('message') }}');
        @endif
    </script>
@endsection
