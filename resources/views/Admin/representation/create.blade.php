@extends('Admin.master')

@section('content')
    <div class="card">
        <div class="card-body">
            <form class="form-horizontal" role="form" method="post" action="{{route('representation.store')}}"
                  enctype="multipart/form-data">
                @csrf
                <div class="row">
                    @include('Admin.layouts.errors')

                    <div class="col-12 col-md-4 mb-2">
                        <label>عنوان فارسی(این عنوان در سایت نمایندگی درج می شود)</label>
                        <input type="text" value="{{old('fa_name')}}"
                               class="form-control"
                               name="fa_name" required>
                    </div>

                    <div class="col-12 col-md-4 mb-2">
                        <label>نشان اینترنتی(url)</label>
                        <input type="text" value="{{old('en_name')}}"
                               class="form-control"
                               name="en_name" required>
                    </div>

                    <div class="col-12 col-md-4 mb-2">
                        <label>نام و نام خانوادگی نماینده </label>
                        <input type="text" value="{{old('ageency_name')}}"
                               class="form-control"
                               name="agency_name" required>
                    </div>

                    <div class="col-12 col-md-4 mb-2">
                        <label>کدملی نماینده </label>
                        <input type="text" value="{{old('agency_melicode')}}"
                               class="form-control"
                               name="agency_melicode" required>
                    </div>

                    <div class="col-12 col-md-4 mb-2">
                        <label>عدد پیامک</label>
                        <input type="text" value="{{old('sms_code')}}" required
                               class="form-control"
                               name="sms_code">
                    </div>

                    <div class="col-12 col-md-4 mb-2">
                        <label>شماره تماس</label>
                        <input type="text" value="{{old('call')}}"
                               class="form-control"
                               name="call">
                    </div>

                    <div class="col-12 mb-2">
                        <label>توضیح کوتاه هدر</label>
                        <textarea
                            class="form-control"
                            name="header_description">{{old('header_description')}}</textarea>
                    </div>

                    <div class="col-12 mb-2">
                        <label>آدرس</label>
                        <input type="text" value="{{old('address')}}"
                               class="form-control"
                               name="address">
                    </div>

                    <div class="col-12 col-md-4 mb-2">
                        <label>تعداد مجاز برای ثبت نام کالسنتر</label>
                        <input type="number" value="{{old('id')}}"
                               class="form-control"
                               name="call_number">
                    </div>
                    <div class="col-12 col-md-4 mb-2">
                        <label>تعداد مجاز برای ثبت نام بازاریاب حضوری</label>
                        <input type="number" value="{{old('id')}}"
                               class="form-control"
                               name="marketer_number">
                    </div>

                    <div class="col-12 col-md-4 mb-3">
                        <label>وضعیت</label>

                        <select class="form-control" name="status">
                            <option value="0">
                                غیر فعال
                            </option>
                            <option value="1">
                                فعال
                            </option>
                        </select>
                    </div>

                    <div class="col-12 mb-2">
                        <label>خدمت ها</label>
                        <select class="form-control select2" name="service_id[]" multiple
                                id="confirm">
                            @foreach($services as $service)
                                <option value="{{ $service->id }}">{{ $service->title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-4 mt-2">
                        <div class="card">
                            <div id="stamp_src" class="show">
                                <div class="d-flex align-items-center justify-content-center bg-light text-muted" style="height:150px;">بدون تصویر</div>
                            </div>
                            <div class="card-body">
                                <label>مهر</label>
                                <br>
                                <label for="stamp" id="up-label" class="btn btn-primary custom-file-upload border border-1"><i class="fa fa-upload ml-2" aria-hidden="true"></i> انتخاب تصویر </label>
                                <input name="stamp"  type="file" id="stamp" class="filestyle file-upload file_upload_ajax file-up" data-buttonname="btn-white" accept=".jpg,.jpeg,.png">
                                <input type="hidden" id="stamp_path" class="path" name="urlstamp">
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-8 mt-2">
                        <div class="card">
                            <div id="image_src" class="show">
                                <div class="d-flex align-items-center justify-content-center bg-light text-muted" style="height:150px;">بدون تصویر</div>
                            </div>
                            <div class="card-body">
                                <label>تصویر سایت</label>
                                <br>
                                <label for="image" id="up-label" class="btn btn-primary custom-file-upload border border-1"><i class="fa fa-upload ml-2" aria-hidden="true"></i> انتخاب تصویر </label>
                                <input name="image"  type="file" id="image" class="filestyle file-upload file_upload_ajax file-up" data-buttonname="btn-white" accept=".jpg,.jpeg,.png">
                                <input type="hidden" id="image_path" class="path" name="urlimage">
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <input type="submit" class="btn btn-success border-0" value="ثبت">
                    </div>

                </div>
            </form>
        </div>
    </div>
@stop
