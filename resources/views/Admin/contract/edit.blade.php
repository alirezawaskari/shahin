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
                        <label>فایل آپلود شده :</label>
                        <a href="{{$contract->file}}">مشاهده</a>
                    </div>
                    <div class="col-12 col-md-6 mb-3">
                        <label for="file_upload_file" class="custom-file-upload">
                            <i class="fa fa-cloud-upload"></i> بارگذاری فایل
                        </label>
                        <input id="file_upload_file" class="file-upload file_upload_ajax file-up" type="file"
                               name="image"/>
                        <div id="image_src" class="show"></div>
                        <input type="hidden" id="image" class="path" name="file_path" value="">
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
