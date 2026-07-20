@extends('Admin.master')
@section('content')
    <div class="card">
        <div class="card-body">
            <form class="form-horizontal" role="form" method="post" action="{{route('recruitmentNotice.store')}}"
                  enctype="multipart/form-data">
                @csrf
                <div class="row">
                    @include('Admin.layouts.errors')

                    <div class="col-12 col-md-3 mb-2">
                        <label>عنوان </label>
                        <input type="text" class="form-control" name="title" value="{{old('title')}}" required>
                    </div>
                    <div class="col-12 col-md-3 mb-2">
                        <label>استان ها</label>
                        <select class="form-control" name="province_id"
                                id="confirm">
                            <option value="">استخدام کشوری (دورکاری)</option>
                            @foreach($provinces as $province)
                                <option value="{{ $province->id }}">{{ $province->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if(!auth()->user()->admin_representation_id)
                    <div class="col-12 col-md-3 mb-3">
                        <label>نمایندگی *</label>
                        <select name="representation_id" class="custom-select">
                            @foreach($representations as $representation)
                                <option value="">نمایندگی کل</option>
                                <option value="{{$representation->id}}">{{$representation->fa_name}}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div class="col-12 col-md-3 mb-3">
                        <label>وضعیت نمایش</label>
                        <select class="form-control" name="status">
                            <option value="1">
                                نمایان
                            </option>
                            <option value="0">
                                پنهان
                            </option>
                        </select>
                    </div>
                    <div class="col-12 mb-2">
                        <label>توضیح</label>
                        <textarea  name="description">{{old('description')}}</textarea>
                    </div>
                    <div class="col-12 col-md-4 mt-2">
                        <div class="card">
                            <div class="card-body">
                                <div id="image_src" class="show">
                                    <div class="rounded border d-flex align-items-center justify-content-center text-muted mb-2 mt-2"
                                        style="height:150px;background:#f8f9fa;">بدون تصویر</div>
                                </div>
                                <label>تصویر لوگو</label>
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
@section('js')
<script src="/assets/dashboard/js/layouts.js"></script>
<script src="/assets/script/ckeditor/ckeditor.js"></script>
<script>
    CKEDITOR.replace('description',ck_uploader);
</script>
@endsection
