@extends('Admin.master')

@section('css')

@stop

@section('content')

    <div class="card">
        <div class="card-body">
            <div class="x_title">
                <h4>ثبت قرارداد</h4>
                <div class="clearfix"></div>
            </div>
            <form class="form-horizontal" method="post"
                  action="{{route('contract.store')}}" enctype="multipart/form-data">
                {{csrf_field()}}
                <div class="row">
                    <div class="col-12 col-md-4 mb-3">
                        <label>عنوان *</label>
                        <input type="text" class="form-control" placeholder="لطفا عنوان را وارد کنید"
                               name="title"
                               value="{{old('title')}}">
                        @if($errors->has('title'))
                            <span class="help-block sub-error "> {{$errors->first('title')}} </span>
                        @endif

                    </div>
                    <br/>
                    <div class="col-12 col-md-4 mb-3">
                        <label>نوع *</label>
                        <select name="type" class="form-control" title="">
                            <option value="">لطفا انتخاب کنید..</option>
                            <option value="1">خرید</option>
                            <option value="2">فروش</option>
                            <option value="3">واگذاری</option>
                        </select>
                        @if($errors->has('type'))
                            <span class="help-block sub-error"> {{$errors->first('type')}} </span>
                        @endif

                    </div>
                    <div class="col-12 col-md-4 mb-3">
                        <label>پوشه</label>
                        <select name="contract_folder_id" class="form-control" title="پوشه">
                            <option value="">دسته بندی نشده</option>
                            @foreach($folders as $folderId => $folderTitle)
                                <option value="{{ $folderId }}"
                                        @if(old('contract_folder_id') == $folderId) selected @endif>{{ $folderTitle }}</option>
                            @endforeach
                        </select>
                        @if($errors->has('contract_folder_id'))
                            <span class="help-block sub-error">{{ $errors->first('contract_folder_id') }}</span>
                        @endif
                    </div>
                    <br/> <br/>
                    <div class="col-12 col-md-4 mb-3">
                        <label>فایل</label><br/>
                        <label for="image" class="btn btn-primary custom-file-upload border border-1">
                            <i class="fa fa-cloud-upload"></i> بارگذاری
                        </label>
                        <input id="image" class="d-none file-upload file_upload_ajax" type="file"
                               name="image"/>
                        <div id="image_src" class="show"></div>
                        <input type="hidden" id="image_path" class="path" name="file_path" value="">
                        @if($errors->has('file_path'))
                            <span class="help-block sub-error"> {{$errors->first('file_path')}} </span>
                        @endif
                    </div>
                    <br/>
                    <div class="col-12 col-md-10 mb-3">
                        <label>فایل‌های مدیریت (حداکثر ۵ فایل - اختیاری)</label>
                        <input type="file" name="staff_files[]" class="form-control form-control-sm" multiple data-file-picker {!! \App\Services\V1\Admin\ContractMediaService::pickerAttributes() !!}
                               accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                        <small class="text-muted">فرمت‌های مجاز: pdf، doc، docx، jpg، jpeg، png - حداکثر ۱۰ مگابایت برای هر فایل</small>
                        @if($errors->has('staff_files'))
                            <span class="help-block sub-error d-block">{{ $errors->first('staff_files') }}</span>
                        @endif
                        @foreach($errors->get('staff_files.*') as $fileErrors)
                            @foreach($fileErrors as $fileError)
                                <span class="help-block sub-error d-block">{{ $fileError }}</span>
                            @endforeach
                        @endforeach
                    </div>
                    <br/>
                    <div class="col-12 col-md-10 mb-3">
                        <label>محتوا </label>
                        <textarea class="form-control" name="content" rows="5"
                                  cols="5" title="محتوا">{{old('content')}}</textarea>
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
@include('Admin.contract.partials.file-picker')

@stop
