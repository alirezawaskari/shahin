@extends('Admin.master')
@section('title'){{ $service->title }}@endsection

@section('css')

@stop

@section('content')
<div class="page-header">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                <li class="breadcrumb-item active" aria-current="page">ویرایش آیتم {{ $service->title }}</li>
            </ol>
        </nav>
    </div>
</div>
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
                <a class="nav-link" id="text-tab" data-toggle="tab" href="#profile" role="tab" aria-controls="profile"
                    aria-selected="false"><i class="fa fa-text-height"></i> متن</a>
            </li>
            <li class="nav-item  flex-fill text-center nav-item-service">
                <a class="nav-link  " id="firstImages-tab" data-toggle="tab" href="#contact" role="tab"
                    aria-controls="contact" aria-selected="false"> <i class="fa fa-image"></i> تصاویر اولیه</a>
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
                    تصاویر اسلایدر دیتاماینینگ
                </a>
            </li>
        </ul>
        <div class="tab-content" id="myTabContent">

            <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="firstData-tab">
                <div class="container m-top-40">
                    <form action="{{route('serviceItem.update' , [$service->slug ])}}" id="basic-information-form">
                        <input type="hidden" name="update_type" value="basic_information">
                        <div class="row">
                            <div class="col-12 col-md-3 mb-3">
                                <label>عنوان</label>
                                <input type="text" value="{{ $service->title }}" class="form-control" name="title"
                                    required>
                            </div>

                            <div class="col-12 col-md-9 mb-3">
                                <label>عنوان لاتین (توجه داشته باشید که این عنوان در url نمایش داده میشود و حتما باید به
                                    صورت انگلیسی وارد شود.)</label>
                                <input type="text" value="{{ $service->en_title }}" class="form-control" name="en_title"
                                    required>
                            </div>
                            <div class="col-12 col-md-12 mb-3">
                                <label>توضیحات</label>
                                <input type="text" value="{{ $service->description }}" class="form-control"
                                    name="description" required>
                            </div>

                            <div class="col-12 col-md-4 mb-3">
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
                            <div class="col-12 col-md-4 mb-3">
                                <label>نمایش در سایت</label>
                                <select class="form-control" name="isShow">
                                    <option value="1" {{ $service->isShow == true ? 'selected' : '' }}>
                                        فعال
                                    </option>
                                    <option value="0" {{ $service->isShow == false ? 'selected' : '' }}>
                                        غیر فعال
                                    </option>
                                </select>
                            </div>
                            <div class="col-12 col-md-4 mb-5">
                                <label>عنوان منو</label>

                                <input id="menu_title_lbl" type="text" class="form-control mb-2" name="menu_title"
                                    value="{{isset($service->menu->title) ? $service->menu->title:''}}"
                                    placeholder="لطفا عنوان منو را وارد کنید">

                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="is_menu" value="1" @if($service->menu()->exists())
                                        checked
                                        @endif> نمایش در منو
                                    </label>
                                    @if($errors->has('is_menu'))<span
                                        class="help-block sub-error">{{$errors->first('is_menu')}}</span>@endif
                                </div>
                            </div>
                            <div class="col-12 col-md-12 mb-3">
                                <span class="btn btn-success" id="basic-information-submit">ثبت تغییرات</span>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <!--__________________________________________-->
            <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="text-tab">
                <div class="container m-top-40">
                    <form action="{{route('serviceItem.update' , [$service->slug ])}}" id="text-information-form">
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
                    <form action="{{route('serviceItem.update' , [$service->slug ])}}" id="first-images-form"
                        method="post" enctype="multipart/form-data">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" value="first_images" name="update_type">
                        @if(ghorfe())
                        <div class="row">
                            <div class="col-12 col-md-4 mb-3 border">
                                @include('Admin.partials.currentMedia', ['owner' => $service, 'field' => 'image'])
                                <label>تصویر خدمت (قابل نمایش در صفحه اول)</label> <label for="image"
                                    class="btn btn-primary custom-file-upload border border-1">
                                    <i class="fa fa-cloud-upload"></i> انتخاب تصویر
                                </label>
                                <input id="image" class="d-none form-control file-upload file_upload_ajax" type="file"
                                    name="image" accept="image/*" />
                                <input type="hidden" id="image_path" class="path" name="image_path" value="">
                                <div id="image_src" class="show"></div>
                                <small class="text-muted d-block mt-1">
                                    فرمت‌های مجاز: jpg, png, webp | حداکثر ۲ مگابایت | سایز 283*390
                                </small>
                            </div>
                            <div class="col-12 col-md-4 mb-3 border">
                                @include('Admin.partials.currentMedia', ['owner' => $service, 'field' => 'icon'])
                                <label>تصویر آیکن (قابل نمایش در صفحه اول )</label> <label for="icon"
                                    class="btn btn-primary custom-file-upload border border-1">
                                    <i class="fa fa-cloud-upload"></i> انتخاب تصویر
                                </label>
                                <input id="icon" class="d-none form-control file-upload file_upload_ajax" type="file"
                                    name="icon" accept="image/*" />
                                <input type="hidden" id="icon_path" class="path" name="icon_path" value="">
                                <div id="icon_src" class="show"></div>
                                <small class="text-muted d-block mt-1">
                                    فرمت‌های مجاز: jpg, png, webp | حداکثر ۲ مگابایت | سایز 80*80
                                </small>
                            </div>
                            <div class="col-12 col-md-4 mb-3 border">
                                @include('Admin.partials.currentMedia', ['owner' => $service, 'field' => 'thumb'])
                                <label>تصویر اصلی (قابل نمایش در صفحه خدمت)</label>
                                <label for="thumb" class="btn btn-primary custom-file-upload border border-1">
                                    <i class="fa fa-cloud-upload"></i> انتخاب تصویر
                                </label>
                                <input id="thumb" class="d-none form-control file-upload file_upload_ajax" type="file"
                                    name="thumb" accept="image/*" />
                                <input type="hidden" id="thumb_path" class="path" name="thumb_path" value="">
                                <div id="thumb_src" class="show"></div>
                                <small class="text-muted d-block mt-1">
                                    فرمت‌های مجاز: jpg, png, webp | حداکثر ۲ مگابایت | سایز 350*1450
                                </small>
                            </div>
                        </div>
                        @else
                        <div class="row">
                            <div class="col-12 col-md-3 mb-3 border border">
                                @include('Admin.partials.currentMedia', ['owner' => $service, 'field' => 'img_box_1'])
                                <label>تصویر بخش اول</label>
                                <label for="img_box_1" class="btn btn-primary custom-file-upload border border-1">
                                    <i class="fa fa-cloud-upload"></i> انتخاب تصویر
                                </label>
                                <input id="img_box_1" class="d-none form-control file-upload file_upload_ajax"
                                    type="file" name="img_box_1" accept="image/*" />
                                <input type="hidden" id="img_box_1_path" class="path" name="img_box_1_path" value="">
                                <div id="img_box_1_src" class="show"></div>
                            </div>

                            <div class="col-12 col-md-3 mb-3 border">
                                @include('Admin.partials.currentMedia', ['owner' => $service, 'field' => 'thumb'])
                                <label>تصویر اسلاید زیر فرم ثبت نام</label>
                                <label for="thumb" class="btn btn-primary custom-file-upload border border-1">
                                    <i class="fa fa-cloud-upload"></i> انتخاب تصویر
                                </label>
                                <input id="thumb" class="d-none form-control file-upload file_upload_ajax" type="file"
                                    name="thumb" accept="image/*" />
                                <input type="hidden" id="thumb_path" class="path" name="thumb_path" value="">
                                <div id="thumb_src" class="show"></div>

                            </div>
                            <div class="col-12 col-md-3 mb-3 border">
                                @include('Admin.partials.currentMedia', ['owner' => $service, 'field' => 'image'])
                                <label>تصویر بخش دوم</label>
                                <label for="image" class="btn btn-primary custom-file-upload border border-1">
                                    <i class="fa fa-cloud-upload"></i> انتخاب تصویر
                                </label>
                                <input id="image" class="d-none form-control file-upload file_upload_ajax" type="file"
                                    name="image" accept="image/*" />
                                <input type="hidden" id="image_path" class="path" name="image_path" value="">
                                <div id="image_src" class="show"></div>

                            </div>
                            <div class="col-12 col-md-3 mb-3 border">
                                @include('Admin.partials.currentMedia', ['owner' => $service, 'field' => 'image_app'])
                                <label>تصویر اصلی (استفاده شده درصفحه اول تک صفحه ای و فهرست خدمات)</label>
                                <label for="image_app" class="btn btn-primary custom-file-upload border border-1">
                                    <i class="fa fa-cloud-upload"></i> انتخاب تصویر
                                </label>
                                <input id="image_app" class="d-none form-control file-upload file_upload_ajax"
                                    type="file" name="image_app" accept="image/*" />
                                <input type="hidden" id="image_app_path" class="path" name="image_app_path" value="">
                                <div id="image_app_src" class="show"></div>
                            </div>
                            <div class="col-12 col-md-3 mb-3 border">
                                @include('Admin.partials.currentMedia', ['owner' => $service, 'field' => 'icon'])
                                <label>(استفاده شده در صفحه اول سایت) تصویر آیکن</label>
                                <label for="icon" class="btn btn-primary custom-file-upload border border-1">
                                    <i class="fa fa-cloud-upload"></i> انتخاب تصویر
                                </label>
                                <input id="icon" class="d-none form-control file-upload file_upload_ajax" type="file"
                                    name="icon" accept="image/*" />
                                <input type="hidden" id="icon_path" class="path" name="icon_path" value="">
                                <div id="icon_src" class="show"></div>
                            </div>
                        </div>
                        @endif
                        <div class="col-12 col-md-12 mb-3">
                            <span class="btn btn-success" id="first-images-submit">ثبت تغییرات</span>
                        </div>
                    </form>
                </div>
            </div>
            <!--__________________________________________-->
            <div class="tab-pane fade" id="question-data" role="tabpanel" aria-labelledby="questionData-tab">
                <div class="container m-top-40">
                    <form action="{{route('serviceItem.update' , [$service->slug ])}}" id="question-insert-form">
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
                        <form action="{{route('serviceItem.update' , [$service->slug ])}}"
                            id="portfolio-information-form">
                            <input type="hidden" name="update_type" value="portfolio_images">

                            <div class="col-12 col-md-12 mb-4" id="portfolio_list_wrap" @if($service->
                                getMedia('portfolio')->count() == 0) style="display:none" @endif>
                                <label> نمونه کار های ذخیره شده</label>
                                <table class="table table-striped">
                                    <tbody id="portfolio_list" data-title-name="existing_portfolio" data-box-h="80">
                                        @foreach($service->getMedia('portfolio') as $media)
                                        <tr class="row_image" id="media_{{$media->id}}">
                                            <td>
                                                <img src="{{$media->getFullUrl()}}"
                                                    alt="{{$media->getCustomProperty('title')}}" class="rounded border"
                                                    style="height:80px;width:auto;object-fit:contain;background:#f8f9fa;">
                                            </td>
                                            <td>
                                                <input type="text" class="form-control" placeholder="عنوان"
                                                    name="existing_portfolio[{{$media->id}}][title]"
                                                    value="{{$media->getCustomProperty('title')}}">
                                            </td>
                                            <td>
                                                <a data-id="{{$media->id}}" title="حذف عکس"
                                                    class="btn btn-sm remove_media_btn"><i class="fa fa-close"></i></a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            @if($service->getMedia('portfolio')->count() == 0 && $service->hasLegacyWorkSampleRows('portfolio'))
                            <div class="col-12 col-md-12 mb-4">
                                <label> نمونه کار های ذخیره شده</label>
                                <small class="d-block text-warning mb-2">تصاویر قدیمی - با ذخیره به سیستم جدید منتقل می‌شوند</small>
                                <table class="table table-striped">
                                    <tbody>
                                        @foreach($service->legacyWorkSampleItems('portfolio') as $item)
                                        <tr>
                                            <td>
                                                <img src="{{ $item['url'] }}" alt="{{ $item['title'] }}"
                                                    class="rounded border"
                                                    style="height:80px;width:auto;object-fit:contain;background:#f8f9fa;">
                                            </td>
                                            <td>{{ $item['title'] }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @endif

                            <div class="col-md-12 col-sm-12 col-xs-12 mb-5">
                                @include('Admin.partials.multipleImages',['has_link'=>0,'listKey'=>'portfolio'])
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
                    <form action="{{route('serviceItem.update' , [$service->slug ])}}" id="site-images-form">
                        <input type="hidden" name="update_type" value="site_images">
                        <div class="row">
                            @include('Admin.partials.editBannerHomePage',['model'=>$service])
                        </div>
                        <span class="btn btn-success" id="site-images-submit">ثبت تغییرات</span>
                    </form>
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
