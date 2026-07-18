@extends('Admin.master')

@section('css')

@stop

@section('content')

    <div class="card">
        <div class="card-body">
            <div class="x_title">
                <h4>ویرایش قرارداد</h4>
                <div class="clearfix"></div>
            </div>
            <form class="form-horizontal" method="post"
                  action="{{route('contract.update',$contract->id)}}" enctype="multipart/form-data">
                {{csrf_field()}}   {{ method_field('PATCH') }}
                <div class="row">
                    <div class="col-12 col-md-4 mb-3">
                        <label>ارجاع به </label>
                        <select name="status" class="form-control" title="status">
                            <option value="">لطفا انتخاب کنید..</option>
                            @foreach($contract::STATUS as $ind=>$status)
                                @if($ind != $contract->status)
                                <option value="{{$ind}}" @if($contract->type == $ind) selected @endif>{{$status}}</option>
                                @endif
                            @endforeach
                        </select>
                        @if($errors->has('status'))
                            <span class="help-block sub-error "> {{$errors->first('status')}} </span>
                        @endif
                    </div>
                    <div class="col-12 col-md-4 mb-3">
                        <label>عنوان *</label>
                        <input type="text" class="form-control" placeholder="لطفا عنوان را وارد کنید"
                               name="title"
                               value="{{$contract->title}}">
                        @if($errors->has('title'))
                            <span class="help-block sub-error "> {{$errors->first('title')}} </span>
                        @endif

                    </div>
                    <br/>
                    <div class="col-12 col-md-4 mb-3">
                        <label>نوع *</label>
                        <select name="type" class="form-control" title="type">
                            <option value="">لطفا انتخاب کنید..</option>
                            <option value="1" @if($contract->type == 1) selected @endif>خرید</option>
                            <option value="2" @if($contract->type == 2) selected @endif>فروش</option>
                            <option value="3" @if($contract->type == 3) selected @endif>واگذاری</option>
                        </select>
                        @if($errors->has('type'))
                            <span class="help-block sub-error"> {{$errors->first('type')}} </span>
                        @endif
                    </div>
                    <br/> <br/> <br/> <br/>
                    <div class="col-12 col-md-6 mb-3">
                        <label class="d-block">فایل آپلود شده :</label>

                        {{-- Contract::getFileAttribute() is Spatie-first with a legacy-column
                             fallback, so $contract->file resolves for both. It's a document
                             (pdf/doc), so it's shown as a link rather than the image-oriented
                             currentMedia partial.

                             Deliberately NO remove (X) here: the file is REQUIRED (store() validates
                             file_path), so deleting it would strand the record in a state the form
                             can't create. The 'file' collection is singleFile(), so uploading a new
                             one replaces it — changing the file never needs a delete. --}}
                        @php $__file = $contract->getFirstMedia('file'); @endphp
                        @if($__file)
                            <a href="{{ $__file->getFullUrl() }}" target="_blank" rel="noopener"
                               class="btn btn-primary py-1 px-2">مشاهده</a>
                        @elseif($contract->file)
                            <a href="{{ $contract->file }}" target="_blank" rel="noopener"
                               class="btn btn-primary py-1 px-2">مشاهده</a>
                            <small class="d-block text-warning mt-1">فایل قدیمی - با ذخیره به سیستم جدید منتقل می‌شود</small>
                        @else
                            <span class="text-muted">فایلی بارگذاری نشده است</span>
                        @endif
                    </div>
                    <div class="col-12 col-md-6 mb-3">
                        <label for="image" class="btn btn-primary custom-file-upload border border-1">
                            <i class="fa fa-cloud-upload"></i> بارگذاری فایل
                        </label>
                        <input id="image" class="d-none file-upload file_upload_ajax file-up" type="file"
                               name="image"/>
                        <div id="image_src" class="show"></div>
                        <input type="hidden" id="image_path" class="path" name="file_path" value="">
                        @if($errors->has('file_path'))
                            <span class="help-block sub-error"> {{$errors->first('file_path')}} </span>
                        @endif
                    </div>
                    <br/>
                    <div class="col-12 col-md-10 mb-3">
                        <label>محتوا </label>
                        <textarea class="form-control" name="content" rows="5"
                                  cols="5" title="محتوا">{{$contract->content}}</textarea>
                        @if($errors->has('content'))
                            <span class="help-block sub-error"> {{$errors->first('content')}} </span>
                        @endif
                    </div>
                    <br/>

                    <br/>
                    <div class="col-12 col-md-12 mb-3">
                        <a href="/dashboard/contract" class="btn btn-warning">بازگشت</a>
                        <button type="submit" class="btn btn-success">ارسال</button>
                    </div>
                </div>
            </form>

        </div>
    </div>

@stop
@section('js')

@stop
