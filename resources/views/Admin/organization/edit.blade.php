@extends('Admin.master')

@section('content')
    <div class="card">
        <div class="card-body">
            <form class="form-horizontal" role="form" method="post" action="{{route('organization.update' , [$organization->slug])}}"
                  enctype="multipart/form-data">
                @csrf
                @method('PATCH')

                <div class="row">
                    @include('Admin.layouts.errors')

                    <div class="col-12 col-md-3 mb-2">
                        <label>شماره بایگانی</label>
                        <input type="text" value="{{ $organization->archive_number }}"
                               class="form-control"
                               name="archive_number" required>
                    </div>

                    <div class="col-12 col-md-3 mb-2">
                        <label>عنوان فارسی</label>
                        <input type="text" value="{{ $organization->fa_name }}"
                               class="form-control"
                               name="fa_name" required>
                    </div>

                    <div class="col-12 col-md-3 mb-2">
                        <label>عنوان لاتین</label>
                        <input type="text" value="{{ $organization->en_name }}"
                               class="form-control"
                               name="en_name" required>
                    </div>
                    <div class="col-12 col-md-3 mb-2">
                        <label>نام نماینده سازمان</label>
                        <input type="text" class="form-control" name="agent_organ" value="{{ $organization->agent_organ }}" required>
                    </div>
                    <div class="col-12 col-md-3 mb-2">
                        <label>توضیح کوتاه هدر</label>
                        <input class="form-control" name="header_description" value="{{ $organization->header_description }}">
                    </div>

                    <div class="col-12 col-md-3 mb-2">
                        <label>عدد پیامک</label>
                        <input type="text" value="{{ $organization->sms_code ? implode(',' , $organization->sms_code) : 'ندارد' }}" required
                               class="form-control"
                               name="sms_code">
                    </div>

                    <div class="col-12 col-md-3  mb-2">
                        <label>شماره تماس</label>
                        <input type="text" value="{{ $organization->call }}"
                               class="form-control"
                               name="call">
                    </div>

                    <div class="col-12 col-md-3 mb-2">
                        <label>میزان اعتبار(ریال)</label>
                        <input type="text" name="credit" value="{{ number_format($organization->credit) }}" class="form-control" oninput="separatePrices(this)">
                    </div>

                    <div class="col-12 mb-2">
                        <label>آدرس</label>
                        <input type="text" value="{{ $organization->address }}"
                               class="form-control"
                               name="address">
                    </div>

                    <div class="col-12 col-md-4 mb-2">
                        <label> خدمت های فعال</label>
                        <select class="form-control select2" name="service_id[]" multiple id="confirm">
                            @foreach($services as $service)
                                <option value="{{ $service->id }}" {{ in_array(trim($service->id) , $organization->services()->pluck('id')->toArray()) ? 'selected' : '' }}>{{ $service->title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-4 mb-3">
                        <label>وضعیت</label>

                        <select class="form-control" name="status">
                            <option value="0" {{ $organization->status == 0 ? 'selected' : '' }}>
                                غیر فعال
                            </option>
                            <option value="1" {{ $organization->status == 1 ? 'selected' : '' }}>
                                فعال
                            </option>
                        </select>
                    </div>

                    <div class="col-12 col-md-4 mb-3">
                        <label>وضعیت بایگانی</label>

                        <select class="form-control" name="archive_status">
                            <option value="0" {{ $organization->archive_status == 0 ? 'selected' : '' }}>
                                 راکد
                            </option>
                            <option value="1" {{ $organization->archive_status == 1 ? 'selected' : '' }}>
                                نیمه جاری
                            </option>
                            <option value="2" {{ $organization->archive_status == 2 ? 'selected' : '' }}>
                                 جاری
                            </option>
                        </select>
                    </div>

                    <div class="col-12 col-md-4 mt-2">
                        <div class="card">
                            @if($organization->stamp)
                            <div id="stamp_src" class="show">
                            <a href="{{ $organization->stamp }}" target="_blank">
                                <img src="{{ $organization->stamp }}" class="card-img-top" height="150px">
                            </a>
                            @if($organization->mediaIsLegacy('stamp'))
                            <small class="d-block text-warning mt-1 px-2">تصویر قدیمی - با ذخیره به سیستم جدید منتقل می‌شود</small>
                            @endif
                            </div>
                            @else
                            <div id="stamp_src" class="show">
                                <img class="card-img-top" height="150px">
                            </div>
                            @endif
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
                            @if($organization->image)
                            <div id="image_src" class="show">
                                <a href="{{ $organization->image }}" target="_blank">
                                    <img id="image_src" src="{{ $organization->image }}" class="card-img-top" height="150px">
                                </a>
                                @if($organization->mediaIsLegacy('image'))
                                <small class="d-block text-warning mt-1 px-2">تصویر قدیمی - با ذخیره به سیستم جدید منتقل می‌شود</small>
                                @endif
                            </div>
                            @else
                            <div id="image_src" class="show">
                                <img class="card-img-top" height="150px">
                            </div>
                            @endif
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
                            <div id="document_src" class="show"></div>
                            <input type="hidden" id="document" class="path"
                                name="urldocument[]">
                        </div>
                    </div>

                    @if(isset($organization->document))
                    @foreach($organization->document as $image)
                    <div class="card col-md-3">
                        <a href="{{ $image }}" target="_blank">
                            <img src="{{ $image }}" class="card-img-top" height="150px">
                        </a>
                    </div>
                    @endforeach
                    @if($organization->arrayMediaHasLegacy('document'))
                    <div class="col-12"><small class="d-block text-warning mt-1">فایل‌های قدیمی - با ذخیره به سیستم جدید منتقل می‌شوند</small></div>
                    @endif
                    @endif
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
