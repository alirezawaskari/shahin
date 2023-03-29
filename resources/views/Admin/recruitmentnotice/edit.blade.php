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

                    <div class="col-12 col-md-4 mb-2">
                        <label>عنوان </label>
                        <input type="text" value="{{ $recruitmentNotice->title }}"
                               class="form-control"
                               name="title" required>
                    </div>
                    <div class="col-12 col-md-4 mb-2">
                        <label>استان ها</label>
                        <select class="form-control" name="province_id" id="confirm">
                            @foreach($provinces as $province)
                                <option value="{{ $province->id }}" {{ $recruitmentNotice->province_id ==  $province->id  ? 'selected' : '' }}>{{ $province->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-4 mb-3">
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
                            @if($recruitmentNotice->image)
                            <div id="image_src" class="show">
                            <a href="{{ asset($recruitmentNotice->image) }}" target="_blank">
                                <img src="{{ asset($recruitmentNotice->image) }}" class="card-img-top" height="150px">
                            </a>
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
                                <input type="hidden" id="image" class="path" name="urlimage">
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

