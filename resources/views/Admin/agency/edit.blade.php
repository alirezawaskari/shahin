@extends('Admin.master')

@section('content')
    <div class="card">
        <div class="card-body table-responsive">
            <h4>{{ ' شماره موبایل : '.$agency->phone.' - '.' : نام و نام خانوادگی : '.$agency->name.' - '.' استان : '.$agency->province.' - '.' شهر : '.$agency->city }}</h4>

            <table class="vuetable table table-bordered table-striped table-responsive-md mt-5">
                <thead>
                    <tr>
                        <th>آپدیت کننده</th>
                        <th>تاریخ</th>
                        <th>توضیحات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($comments as $comment)
                        <tr>
                            <td>{{ $comment['user'] }}</td>
                            <td>
                                {{ $comment['date'] }}
                            </td>
                            <td>{{ $comment['description'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <form class="form-horizontal" role="form" method="post"
                  action="{{route('agency.update' , [ $agency->id ])}}" enctype="multipart/form-data">
                @csrf
                @method('PATCH')

                @include('Admin.layouts.errors')
                <div class="row">
                    <div class="col-12 mb-3">
                        <label>توضیحات</label>
                        <textarea type="text"
                                  class="form-control"
                                  name="description"></textarea>
                    </div>

                    <div class="col-12 col-md-4 mb-3">
                        <label>وضعیت</label>
                        <select class="form-control selectpicker" name="status" id="status">
                            <option value="0" {{ $agency->status == 0 ? 'selected' : '' }}>
                                جدید
                            </option>
                            <option value="1" {{ $agency->status == 1 ? 'selected' : '' }}>
                                بررسی شده
                            </option>
                            <option value="2" {{ $agency->status == 2 ? 'selected' : '' }}>
                                تایید شده
                            </option>
                            <option value="3" {{ $agency->status == 3 ? 'selected' : '' }}>
                                تعلیق شده
                            </option>
                            <option value="4" {{ $agency->status == 4 ? 'selected' : '' }}>
                                اعطا شده
                            </option>
                            <option value="5" {{ $agency->status == 5 ? 'selected' : '' }}>
                                نیاز به تماس
                            </option>
                        </select>
                    </div>

                    <div class="col-12 col-md-4 mb-3">
                        <label>تاریخ یادآوری</label>
                        <input class="form-control date mb-2" name="expire_date">
                    </div>

                    <div class="col-12 col-md-4 mb-3">
                        <label>نوع عاملیت</label>
                        <select class="form-control" name="agency_type_id">
                            @foreach($agency_types as $agency_type)
                            <option value="{{ $agency_type->id }}" {{ $agency_type->id == $agency->agency_type_id ? 'selected' : '' }}>{{ $agency_type->title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="card">
                            @if($agency->imgmeli)
                            <div id="imgmeli_src" class="show">
                            <a href="{{ $agency->imgmeli }}" target="_blank" rel="noopener">
                                <img src="{{ $agency->imgmeli }}" class="card-img-top" style="height:150px;width:100%;object-fit:contain;background:#f8f9fa;">
                            </a>
                            @if($agency->mediaIsLegacy('imgmeli'))
                            <small class="d-block text-warning mt-1 px-2">تصویر قدیمی - با ذخیره به سیستم جدید منتقل می‌شود</small>
                            @endif
                            </div>
                            @else
                            <div id="imgmeli_src" class="show">
                                <div class="d-flex align-items-center justify-content-center bg-light text-muted" style="height:150px;">بدون تصویر</div>
                            </div>
                            @endif
                            <div class="card-body">
                                <label>کارت ملی</label>
                                <br>
                                <label for="imgmeli" id="up-label" class="btn btn-primary custom-file-upload border border-1"><i class="fa fa-upload ml-2" aria-hidden="true"></i> انتخاب تصویر </label>
                                <input name="imgmeli"  type="file" id="imgmeli" class="filestyle file-upload file_upload_ajax file-up" data-buttonname="btn-white" accept=".jpg,.jpeg,.png">
                                <input type="hidden" id="imgmeli_path" class="path" name="urlimgmeli">
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="card">
                            @if($agency->imgturnover)
                            <div id="imgturnover_src" class="show">
                            <a href="{{ $agency->imgturnover }}" target="_blank" rel="noopener">
                                <img src="{{ $agency->imgturnover }}" class="card-img-top" style="height:150px;width:100%;object-fit:contain;background:#f8f9fa;">
                            </a>
                            @if($agency->mediaIsLegacy('imgturnover'))
                            <small class="d-block text-warning mt-1 px-2">تصویر قدیمی - با ذخیره به سیستم جدید منتقل می‌شود</small>
                            @endif
                            </div>
                            @else
                            <div id="imgturnover_src" class="show">
                                <div class="d-flex align-items-center justify-content-center bg-light text-muted" style="height:150px;">بدون تصویر</div>
                            </div>
                            @endif
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
    <script src="{{asset('assets/dashboard/js/layouts.js')}}"></script>
@endsection
