@extends('Admin.master')

@section('content')
    @if(!is_null($parent))
        <div class="card">
            <div class="card-body">
                <div class="card-header">
                    سازمان اصلی
                </div>
                <div class="card-body">
                    <div class="container">
                        <div class="row">
                            <div class="col-md-3">
                                <table class="table">
                                    <tr>
                                        <td>عنوان</td>
                                        <td>{{$parent->fa_name}}</td>
                                    </tr>
                                    <tr>
                                        <td>شماره بایگانی</td>
                                        <td>{{$parent->archive_number}}</td>
                                    </tr>
                                    <tr>
                                        <td>نوع همکاری</td>
                                        <td>{{$parent::TYPE[$parent->type]}}</td>
                                    </tr>
                                    <tr>
                                        <td>تاریخ ایجاد</td>
                                        <td>{{verta($parent->created_at)->format('Y/m/d')}}</td>
                                    </tr>
                                    <tr>
                                        <td>وضعیت بایگانی</td>
                                        <td>{{$parent::status($parent->archive_status)}}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
    <div class="card">
        <div class="card-header">
            @if(!is_null($parent))
                افزودن زیر سازمان جدید به {{$parent->fa_name}}
            @endif
        </div>
        <div class="card-body">

            <form class="form-horizontal" role="form" method="post" action="{{route('organization.store')}}"
                  enctype="multipart/form-data">
                @csrf
                <div class="row">
                    @include('Admin.layouts.errors')
                    @if(!is_null($parent))
                    <input type="hidden" name="parent_id" value="{{$parent->id}}">
                    @endif
                        <div class="col-12 col-md-3 mb-2">
                        <label>شماره بایگانی</label>
                        <input type="text" value="{{old('archive_number')}}"
                               class="form-control"
                               name="archive_number" required>
                    </div>

                    <div class="col-12 col-md-3 mb-2">
                        <label>عنوان فارسی</label>
                        <input type="text" value="{{old('fa_name')}}"
                               class="form-control"
                               name="fa_name" required>
                    </div>

                    <div class="col-12 col-md-3 mb-2">
                        <label>عنوان لاتین</label>
                        <input type="text" value="{{old('en_name')}}"
                               class="form-control"
                               name="en_name" required>
                    </div>
                    <div class="col-12 col-md-3 mb-2">
                        <label>نام نماینده سازمان</label>
                        <input type="text" class="form-control" name="agent_organ" required>
                    </div>
                    <div class="col-12 col-md-3 mb-2">
                        <label>توضیح کوتاه هدر</label>
                        <input class="form-control" name="header_description" value="{{old('header_description')}}">
                    </div>

                    <div class="col-12 col-md-3 mb-2">
                        <label>عدد پیامک</label>
                        <input type="text" value="{{old('sms_code')}}" required
                               class="form-control"
                               name="sms_code">
                    </div>

                    <div class="col-12 col-md-3 mb-2">
                        <label>شماره تماس</label>
                        <input type="text" value="{{old('call')}}"
                               class="form-control"
                               name="call">
                    </div>

                    <div class="col-12 col-md-3 mb-3">
                        <label>میزان اعتبار(ریال)</label>
                        <input type="text" name="credit" class="form-control" oninput="separatePrices(this)">
                    </div>

                    <div class="col-12 mb-2">
                        <label>آدرس</label>
                        <input type="text" value="{{old('address')}}"
                               class="form-control"
                               name="address">
                    </div>

                    <div class="col-12 col-md-4 mb-2">
                        <label> خدمت های فعال</label>
                        <select class="form-control select2" name="service_id[]" multiple
                                id="confirm">
                            @foreach($services as $service)
                                <option value="{{ $service->id }}">{{ $service->title }}</option>
                            @endforeach
                        </select>
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

                    <div class="col-12 col-md-4 mb-3">
                        <label>وضعیت بایگانی</label>

                        <select class="form-control" name="archive_status">
                            <option value="0">
                                 راکد
                            </option>
                            <option value="1">
                                نیمه جاری
                            </option>
                            <option value="2">
                                 جاری
                            </option>
                        </select>
                    </div>

                    <div class="col-12 col-md-4 mt-2">
                        <div class="card">
                            <img id="stamp_src" class="card-img-top" height="150px">
                            <div class="card-body">
                                <label>مهر</label>
                                <br>
                                <label for="stamp" id="up-label" class="btn badge-secondary"><i class="fa fa-upload ml-2" aria-hidden="true"></i> انتخاب تصویر </label>
                                <input name="stamp"  type="file" id="stamp" class="filestyle file-upload file_upload_ajax file-up" data-buttonname="btn-white" accept=".jpg,.jpeg,.png">
                                <input type="hidden" id="stamp_path" class="path" name="urlstamp">
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-8 mt-2">
                        <div class="card">
                            <img id="image_src" class="card-img-top" height="150px">
                            <div class="card-body">
                                <label>تصویر سایت</label>
                                <br>
                                <label for="image" id="up-label" class="btn badge-secondary"><i class="fa fa-upload ml-2" aria-hidden="true"></i> انتخاب تصویر </label>
                                <input name="image"  type="file" id="image" class="filestyle file-upload file_upload_ajax file-up" data-buttonname="btn-white" accept=".jpg,.jpeg,.png">
                                <input type="hidden" id="image_path" class="path" name="urlimage">
                            </div>
                        </div>
                    </div>

                    <div class="col-12 row">
                        <div class="col-md-4">
                            <div id="addnew" class="btn btn-success mb-5">
                                <label for="document" id="up-label"><i class="fa fa-upload ml-2"
                                        aria-hidden="true"></i> افزودن مدارک سازمان </label>
                                <input name="document[]" type="file" id="document"
                                    class="filestyle file-upload file_upload_ajax file-up" data-buttonname="btn-white"
                                    accept=".jpg,.jpeg,.png">
                            </div>
                        </div>
                        <div id="document_newlink" class="col-12 row">
                            <div>
                            </div>
                        </div>
                        <div id="document_newlinktpl" class="d-none">
                            <img id="document_src" class="card-img-top mb-2" height="150px">
                            <input type="hidden" id="document_path" class="path"
                                name="urldocument[]">
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
@section('js')
<script src="{{ asset('/assets/dashboard/js/layouts.js?v=2.0') }}"></script>
@endsection
