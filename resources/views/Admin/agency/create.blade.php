@extends('Admin.master')

@section('content')
    <div class="card">
        <div class="card-body">
            <form class="form-horizontal" role="form" method="post"
                  action="{{route('agency.store')}}" enctype="multipart/form-data">
                @csrf

                @include('Admin.layouts.errors')
                <div class="row">

                    <div class="col-12 col-md-4 mb-3">
                        <label>نام</label>
                        <input type="text" value="{{ old('name') }}"
                               class="form-control"
                               name="name" required>
                    </div>

                    <div class="col-12 col-md-4 mb-3">
                        <label>موبایل</label>
                        <input type="text" value="{{ old('phone') }}"
                               class="form-control"
                               name="phone" required>
                    </div>

                    <div class="col-12 col-md-4 mb-3">
                        <label>نوع نمایندگی</label>
                        <select class="form-control" name="agency_type_id">
                            @foreach($agency_types as $agency_type)
                            <option value="{{ $agency_type->id }}">{{ $agency_type->title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-6 mb-3">
                        <label>استان</label>
                        <input type="text" value="{{ old('province') }}"
                               class="form-control"
                               name="province" required>
                    </div>

                    <div class="col-12 col-md-6 mb-3">
                        <label>شهر</label>
                        <input type="text" value="{{ old('city') }}"
                               class="form-control"
                               name="city" required>
                    </div>

                    <div class="col-12 mb-3">
                        <label>توضیحات</label>
                        <textarea type="text"
                                  class="form-control"
                                  name="description">{{ old('description') }}</textarea>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="card">
                            <div id="imgmeli_src" class="show">
                                <div class="d-flex align-items-center justify-content-center bg-light text-muted" style="height:150px;">بدون تصویر</div>
                            </div>
                            <div class="card-body">
                                <label>کارت ملی</label>
                                <br>
                                <label for="imgmeli" id="up-label" class="btn btn-primary custom-file-upload border border-1"><i class="fa fa-upload ml-2" aria-hidden="true"></i> انتخاب تصویر </label>
                                <input name="imgmeli"  type="file" id="imgmeli" class="filestyle file-upload file_upload_ajax file-up" data-buttonname="btn-white" accept=".jpg,.jpeg,.png">
                                <input type="hidden" id="imgmeli_path" class="path" name="urlimgmeli">
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="card">
                            <div id="imgturnover_src" class="show">
                                <div class="d-flex align-items-center justify-content-center bg-light text-muted" style="height:150px;">بدون تصویر</div>
                            </div>
                            <div class="card-body">
                                <label>تصویر پرینت حساب | موجودی حساب</label>
                                <br>
                                <label for="imgturnover" id="up-label" class="btn btn-primary custom-file-upload border border-1"><i class="fa fa-upload ml-2" aria-hidden="true"></i> انتخاب تصویر </label>
                                <input name="imgturnover"  type="file" id="imgturnover" class="filestyle file-upload file_upload_ajax file-up" data-buttonname="btn-white" accept=".jpg,.jpeg,.png">
                                <input type="hidden" id="imgturnover_path" class="path" name="urlimgturnover">
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
@section('js')
    <script src="{{ asset('/assets/dashboard/js/layouts.js?v=2.0') }}"></script>
@endsection
