@extends('Admin.master')

@section('content')
    <div class="card">
        <div class="card-body">
            <form class="form-horizontal" role="form" method="post" action="{{route('singleWebPage.store')}}"
                  enctype="multipart/form-data">
                @csrf
                <div class="row">
                    @include('Admin.layouts.errors')
                    <div class="col-12 col-md-4 mb-2">
                        <label>عنوان فارسی(این عنوان در سایت درج می شود)</label>
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

                    <div class="col-12 mt-2">
                        <div class="card">
                            <div id="image_src" class="show">
                            <img class="card-img-top" height="150px">
                            </div>
                            <div class="card-body">
                                <label>تصویر سایت</label>
                                <br>
                                <label for="image" id="up-label" class="btn badge-secondary"><i class="fa fa-upload ml-2" aria-hidden="true"></i> انتخاب تصویر </label>
                                <input name="image"  type="file" id="image" class="filestyle file-upload file_upload_ajax file-up" data-buttonname="btn-white" accept=".jpg,.jpeg,.png">
                                <input type="hidden" id="image" class="path" name="urlimage">
                            </div>
                        </div>
                    </div>
                    <div class="col-12 mt-2">
                        <label>توضیحات قسمت اول سایت</label>
                        <textarea
                            class="form-control"
                            name="description"
                            id="description"
                        ></textarea>
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
    <script src="{{ asset('/assets/script/ckeditor/ckeditor.js') }}"></script>
    <script>
        $(document).ready(function () {
            CKEDITOR.replace( 'description', ck_uploader);
        })
    </script>
@endsection
