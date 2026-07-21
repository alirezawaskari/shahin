@extends('Admin.master')

@section('content')
    <div class="card">
        <div class="card-body">
            <form class="form-horizontal" role="form" method="post" action="{{route('recruitmentNotice.update' , [$recruitmentNotice->id])}}"
                  enctype="multipart/form-data">
                @csrf
                @method('PATCH')

                <div class="row">
                    @include('Admin.layouts.errors')

                    <div class="col-12 col-md-3 mb-2">
                        <label>عنوان </label>
                        <input type="text" value="{{ $recruitmentNotice->title }}"
                               class="form-control"
                               name="title" required>
                    </div>
                    <div class="col-12 col-md-3 mb-2">
                        <label>استان ها</label>
                        <select class="form-control" name="province_id" id="confirm">
                            <option value="" {{ $recruitmentNotice->province_id ==  null  ? 'selected' : '' }}>استخدام کشوری (دورکاری)</option>
                            @foreach($provinces as $province)
                                <option value="{{ $province->id }}" {{ $recruitmentNotice->province_id ==  $province->id  ? 'selected' : '' }}>{{ $province->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if(!auth()->user()->admin_representation_id)
                    <div class="col-12 col-md-3 mb-3">
                        <label>نمایندگی *</label>
                        <select name="representation_id" class="custom-select">
                            @foreach($representations as $representation)
                                <option value="">نمایندگی کل</option>
                                <option value="{{$representation->id}}" {{ $representation->id == $recruitmentNotice->representation_id ? 'selected' : '' }}>{{$representation->fa_name}}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div class="col-12 col-md-3 mb-3">
                        <label>وضعیت نمایش</label>
                        <select class="form-control" name="status">
                            <option value="1" {{ $recruitmentNotice->status == 1 ? 'selected' : '' }}>
                                نمایان
                            </option>
                            <option value="0" {{ $recruitmentNotice->status == 0 ? 'selected' : '' }}>
                                پنهان
                            </option>
                        </select>
                    </div>
                    <div class="col-12 mb-2">
                        <label>توضیح</label>
                        <textarea name="description">{{ $recruitmentNotice->description }}</textarea>
                    </div>
                    <div class="col-12 col-md-4 mt-2">
                        <div class="card">
                            <div class="card-body">
                                @include('Admin.partials.currentMedia', ['owner' => $recruitmentNotice, 'field' => 'image', 'boxH' => 150])
                                <label>تصویر سایت</label>
                                <br>
                                <label for="image" id="up-label" class="btn btn-primary custom-file-upload border border-1"><i class="fa fa-upload ml-2" aria-hidden="true"></i> انتخاب تصویر </label>
                                <input name="image"  type="file" id="image" class="filestyle file-upload file_upload_ajax file-up" data-buttonname="btn-white" accept=".jpg,.jpeg,.png">
                                <div id="image_src" class="show"></div>
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

