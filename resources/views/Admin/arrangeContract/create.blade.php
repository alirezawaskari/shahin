@extends('Admin.master')

@section('css')

@stop

@section('content')

    <div class="card">
        <div class="card-body">
            <div class="x_title mb-5">
                <h4>تنظیم قرارداد</h4>
                <div class="clearfix"></div>
            </div>

            <form class="form-horizontal" method="post"
                  action="{{route('arrangeContract.store')}}" enctype="multipart/form-data">
                {{csrf_field()}}
                <input type="hidden" name="contract_id" value="{{$contract_id}}">
                <div class="row">
                    <div class="col-12 col-md-6 mb-5">
                        <label>شماره قرارداد *</label>
                        <input type="text" class="form-control" placeholder="لطفا شماره را وارد کنید"
                               name="no" value="{{old('no')}}">
                        @if($errors->has('no'))
                            <span class="help-block sub-error"> {{$errors->first('no')}} </span>
                        @endif

                    </div>

                    <div class="col-12 col-md-6 mb-5">
                        <label>فایل *</label><br/>
                        <label for="image" class="btn btn-primary custom-file-upload border border-1">
                            <i class="fa fa-cloud-upload"></i> بارگذاری
                        </label>
                        <input id="image" class="d-none file-upload file_upload_ajax file-up" type="file"
                               name="image"/>
                        <div id="image_src" class="show"></div>
                        <input type="hidden" id="image_path" class="path" name="file_path" value="">
                        @if($errors->has('file_path'))
                            <span class="help-block sub-error"> {{$errors->first('file_path')}} </span>
                        @endif
                    </div>

                    <div class="col-12 col-md-12 mb-5">
                        <input class="agency_type_selected" type="radio" name="contract_type_selected" title="نمایندگی"
                               value="0">
                        <span class="lblRadioBtn font-weight-bold font16">نمایندگی </span>

                        <input class="center_selected" type="radio" name="contract_type_selected" title="شعبه"
                               value="1">
                        <span class="lblRadioBtn font-weight-bold font16">شعبه </span>

                    </div>


                    <div class="col-12 col-md-4 mb-5 agency_type_box" style="display: none;">
                        <label>نمایندگی *</label>
                        <select name="agency_type_id" class="form-control" title="">
                            <option value="">لطفا انتخاب کنید..</option>
                            @foreach($agency_types as $agency_type)
                                <option value="{{$agency_type->id}}">{{$agency_type->title}}</option>
                            @endforeach
                        </select>
                        @if($errors->has('agency_type_id'))
                            <span class="help-block sub-error"> {{$errors->first('agency_type_id')}} </span>
                        @endif
                    </div>
                    <div class="col-12 col-md-4 mb-5 center_box" style="display: none;">
                        <label>شعبه *</label>
                        <select name="center_id" class="form-control" title="">
                            <option value="">لطفا انتخاب کنید..</option>
                            @foreach($centers as $center)
                                <option value="{{$center->id}}">{{$center->title}}</option>
                            @endforeach
                        </select>
                        @if($errors->has('center_id'))
                            <span class="help-block sub-error"> {{$errors->first('center_id')}} </span>
                        @endif
                    </div>

                    <div class="col-12 col-md-12 mb-5">
                        <a href="/dashboard/arrangeContract" class="btn btn-warning">بازگشت</a>
                        <button type="submit" class="btn btn-success">ارسال</button>
                    </div>
                </div>
            </form>

        </div>
    </div>

@stop
@section('js')
    <script>
        $('.agency_type_selected').click(function () {
            $('.agency_type_box').css({'display': 'block'});
            $('.center_box').css({'display': 'none'});
        });
        $('.center_selected').click(function () {
            $('.agency_type_box').css({'display': 'none'});
            $('.center_box').css({'display': 'block'});
        });
    </script>
@stop
